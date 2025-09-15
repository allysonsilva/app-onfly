<?php

declare(strict_types=1);

namespace Tests\Datasets;

use DateTimeImmutable;

dataset('travel-requests:store', dataset: [
    [
        [
            'destination' => fake()->city(),
            'departure_date' => $departureDate = fake()->dateTimeBetween('+1 month', '+2 months')->format('Y-m-d'),
            'return_date' => (new DateTimeImmutable($departureDate))->modify('+7 days')->format('Y-m-d'),
        ],
    ],
]);
