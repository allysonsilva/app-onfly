<?php

declare(strict_types=1);

namespace Tests\Support;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

// php artisan db:seed --class="\\Tests\\Support\\PopulateDBSeeder" --env=testing
class PopulateDBSeeder extends Seeder
{
    // use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        TravelRequest::query()->forceDelete();
        User::query()->forceDelete();

        $user0 = User::factory()
            ->has(TravelRequest::factory()->count(10))
            ->create(['email' => TestCase::EMAIL_USER_0]);

        TravelRequest::factory()->canceled()->for($user0)->count(5)->create();
        TravelRequest::factory()->approved()->for($user0)->count(5)->create();

        // Criar 5 usuários cada usuário contendo 10 pedidos de viagem
        User::factory()
            ->count(5)
            ->has(TravelRequest::factory()->count(10))
            ->create();
    }
}
