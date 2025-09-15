<?php

declare(strict_types=1);

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->httpUri = '/v1/travel-requests';
});

describe('travel-requests:store', function () {
    it('travel-requests:store - should be authenticated', function () {
        postJson($this->httpUri)
            ->assertUnauthorized()
            ->assertJsonFragment(['message' => 'Unauthenticated.']);
    });

    it('travel-requests:store - should response with error - 422', function () {
        Sanctum::actingAs($this->userAuth(), ['user']);

        postJson($this->httpUri)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'destination',
                'departure_date',
                'return_date',
            ]);
    });

    it('travel-requests:store - successfully', function (array $payload) {
        Sanctum::actingAs($user = $this->userAuth(), ['user']);

        $response = postJson($this->httpUri, $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'code',
                    'requester' => ['name', 'email'],
                    'destination',
                    'departure_date',
                    'return_date',
                    'status'
                ]
            ]);

        assertDatabaseHas(TravelRequest::class, [
            'code' => $response->json('data.code'),
            'user_id' => $user->getKey(),
            'destination' => $payload['destination'],
            'departure_date' => $payload['departure_date'],
            'return_date' => $payload['return_date'],
            'status' => TravelRequestStatus::REQUESTED,
            'canceled_at' => null,
            'approved_at' => null,
        ]);

    })->with('travel-requests:store');
});
