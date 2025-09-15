<?php

declare(strict_types=1);

use App\Enums\TravelRequestStatus;
use App\Errors\TravelRequestAlreadyCancelled;
use App\Errors\TravelRequestNotCancellable;
use App\Models\TravelRequest;
use App\Notifications\TravelRequestStatusChanged;
use App\Support\Errors\HttpErrorStatus;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\deleteJson;

beforeEach(function () {
    $this->httpUri = fn (string $code) => sprintf('/v1/travel-requests/%s', $code);
});

describe('travel-requests:cancel', function () {
    it('travel-requests:cancel - should be authenticated', function () {
        deleteJson(($this->httpUri)('xyz'))
            ->assertUnauthorized()
            ->assertJsonFragment(['message' => 'Unauthenticated.']);
    });

    it('travel-requests:cancel - already cancelled', function () {
        Sanctum::actingAs($user = $this->userAuth(), ['user']);

        Exceptions::fake(TravelRequestAlreadyCancelled::class);

        $travelRequest = TravelRequest::factory()->canceled()->for($user)->create();

        deleteJson(($this->httpUri)($travelRequest->code))
            ->assertForbidden()
            ->assertJson([
                'title' => 'Travel Request Already Cancelled',
                'detail' => 'Cannot cancel a cancelled travel request.',
                'code' => 'travel-request-already-cancelled',
                'status' => HttpErrorStatus::FORBIDDEN->value,
            ]);

        Exceptions::assertReported(TravelRequestAlreadyCancelled::class);
    });

    it('travel-requests:cancel - is past cancellation deadline', function () {
        Sanctum::actingAs($user = $this->userAuth(), ['user']);

        Exceptions::fake(TravelRequestNotCancellable::class);

        $travelRequest = TravelRequest::factory()->approved()->for($user)->create();

        // Está a 5 dias da data de partida
        $mockNow = $travelRequest->departure_date->addDays(5);
        Carbon::setTestNow($mockNow);

        deleteJson(($this->httpUri)($travelRequest->code))
            ->assertForbidden()
            ->assertJson([
                'title' => 'Unable to Cancel Travel Request',
                'detail' => 'The deadline for canceling this travel request has passed. You can no longer cancel this trip.',
                'code' => 'travel-request-not-cancellable',
                'status' => HttpErrorStatus::FORBIDDEN->value,
            ]);

        Exceptions::assertReported(TravelRequestNotCancellable::class);
    });

    it('travel-requests:cancel - successfully', function () {
        Sanctum::actingAs($user = $this->userAuth(), ['user']);

        Notification::fake();

        $travelRequest = TravelRequest::factory()->approved()->for($user)->create();

        // Apenas para fazer a comparação com a data de cancelamento e para passar
        // da verificação dos 7 dias
        $mockNow = $travelRequest->departure_date->subDays(10);
        Carbon::setTestNow($mockNow);

        deleteJson(($this->httpUri)($travelRequest->code))
            ->assertNoContent();

        $travelRequest->refresh();

        expect($travelRequest->canceled_at->toDateString())
            ->toBe($mockNow->toDateString());

        expect($travelRequest->status)
            ->toBe(TravelRequestStatus::CANCELED);

        Notification::assertSentTo([$user], TravelRequestStatusChanged::class);
    });
});
