<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\UpdateTravelRequestStatusController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\TravelRequestController;
use Illuminate\Support\Facades\Route;

// /healthz?view&fresh
// /healthz?exception&fresh
// /healthz?json&fresh
Route::prefix('v1')->get('healthz', HealthCheckController::class)->name('health-check');

Route::prefix('v1')
    ->name('v1.user.')
    ->middleware(['throttle:api', 'auth', 'abilities:user'])
    ->group(function () {
        Route::name('me.')->group(function () {
            Route::get('me/profile', fn () => auth()->user())->name('profile');
        });

        Route::apiResource('travel-requests', TravelRequestController::class)->except('update');
    });

Route::prefix('v1/admin')
    ->name('v1.admin.')
    ->middleware(['throttle:api', 'auth', 'abilities:admin'])
    ->group(function () {
        Route::put(
            'travel-requests/{travel_request}/status',
            UpdateTravelRequestStatusController::class
        )->name('travel-requests.update-status');
    });
