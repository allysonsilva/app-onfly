<?php

declare(strict_types=1);

namespace Database\Factories;

use App\DataObjects\RequesterData;
use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TravelRequest>
 */
class TravelRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'destination' => fake()->city(),
            'departure_date' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'return_date' => fake()->dateTimeBetween('+2 months', '+3 months'),
            'status' => TravelRequestStatus::REQUESTED,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (TravelRequest $travelRequest) {
            $travelRequest->requester = RequesterData::from($travelRequest->user);
        });
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'departure_date' => $departureDate = now()->subMonth(),
            'return_date' => $departureDate->addWeek(),
            'status' => TravelRequestStatus::CANCELED,
            'canceled_at' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'departure_date' => $departureDate = now()->addMonth(),
            'return_date' => $departureDate->addDay(),
            'status' => TravelRequestStatus::APPROVED,
            'approved_at' => now(),
        ]);
    }
}
