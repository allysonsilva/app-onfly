<?php

declare(strict_types=1);

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

beforeEach(function () {
    $this->httpUri = '/v1/travel-requests';
});

describe('travel-requests:list', function () {
    it('travel-requests:list - should be authenticated', function () {
        getJson($this->httpUri)
            ->assertUnauthorized()
            ->assertJsonFragment(['message' => 'Unauthenticated.']);
    });

    it('travel-requests:list - successfully', function () {
        Sanctum::actingAs($this->userAuth(), ['user']);

        // artisan('scout:flush', ['model' => TravelRequest::class])->assertExitCode(0);
        // artisan('scout:import', ['model' => TravelRequest::class])->assertExitCode(0);

        TravelRequest::removeAllFromSearch();
        TravelRequest::all()->searchable();

        $response = getJson(url()->query($this->httpUri, ['per_page' => 10]));

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'requester' => ['name', 'email'],
                        'destination',
                        'departure_date',
                        'return_date',
                        'status'
                    ],
                ],
            ]);
    });

    it('travel-requests:list - filter by status', function () {
        Sanctum::actingAs($this->userAuth(), ['user']);

        TravelRequest::removeAllFromSearch();
        TravelRequest::all()->searchable();

        getJson(url()->query($this->httpUri, ['status' => TravelRequestStatus::CANCELED]))
            ->assertOk()
            ->assertJsonCount(5, 'data');

        getJson(url()->query($this->httpUri, ['status' => TravelRequestStatus::APPROVED]))
            ->assertOk()
            ->assertJsonCount(5, 'data');
    });
});
