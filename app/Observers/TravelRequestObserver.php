<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;

class TravelRequestObserver
{
    /**
     * Handle the TravelRequest "updating" event.
     */
    public function updating(TravelRequest $travelRequest): void
    {
        if ($travelRequest->isDirty('status')) {
            match ($travelRequest->status) {
                TravelRequestStatus::APPROVED => $travelRequest->approved_at = now(),
                TravelRequestStatus::CANCELED => $travelRequest->canceled_at = now(),
                default => null,
            };
        }
    }
}
