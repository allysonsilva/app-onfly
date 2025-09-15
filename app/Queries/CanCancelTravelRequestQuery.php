<?php

declare(strict_types=1);

namespace App\Queries;

use App\Errors\TravelRequestAlreadyCancelled;
use App\Errors\TravelRequestNotCancellable;
use App\Models\TravelRequest;
use App\Support\Contracts\QueryInterface;

final class CanCancelTravelRequestQuery implements QueryInterface
{
    public const CANCEL_DEADLINE_DAYS = 7;

    /**
     * Execute the Query.
     *
     * @param \App\Models\TravelRequest $travelRequest
     */
    public function handle(TravelRequest $travelRequest): true
    {
        if ($travelRequest->status->isCanceled()) {
            throw new TravelRequestAlreadyCancelled()->withContext([
                'code' => $travelRequest->code,
            ]);
        }

        // O cancelamento é permitido se a data da viagem for após (maior que) sete dias a partir de agora.
        $isPastCancellationDeadline = ! $travelRequest
            ->departure_date
            ->isAfter(now()->addDays(self::CANCEL_DEADLINE_DAYS));

        if ($travelRequest->status->isApproved() && $isPastCancellationDeadline) {
            throw new TravelRequestNotCancellable()->withContext([
                'code' => $travelRequest->code,
            ]);
        }

        return true;
    }
}
