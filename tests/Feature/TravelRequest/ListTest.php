<?php

declare(strict_types=1);

use App\Enums\TravelRequestStatus;
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

        $response = getJson(url()->query($this->httpUri, ['per_page' => 10]));

        $response->assertOk()
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

        getJson(url()->query($this->httpUri, ['status' => TravelRequestStatus::CANCELED]))
            ->assertOk();

        getJson(url()->query($this->httpUri, ['status' => TravelRequestStatus::APPROVED]))
            ->assertOk();
    });
});
