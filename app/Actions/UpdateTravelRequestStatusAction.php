<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Errors\TravelRequestAlreadyInStatus;
use App\Support\Actions\LoggedUserAction;
use Illuminate\Support\Facades\DB;

class UpdateTravelRequestStatusAction extends LoggedUserAction
{
    /**
     * Execute the Action.
     *
     * @param \App\Models\TravelRequest $travelRequest
     * @param \App\Enums\TravelRequestStatus $newStatus
     *
     * @return \App\Models\TravelRequest
     */
    public function handle(TravelRequest $travelRequest, TravelRequestStatus $newStatus): TravelRequest
    {
        if ($travelRequest->status === $newStatus) {
            (throw new TravelRequestAlreadyInStatus())->withContext([
                'status' => $newStatus->value,
                'id' => $travelRequest->getKey(),
                'code' => $travelRequest->code,
            ]);
        }

        DB::transaction(function () use ($travelRequest, $newStatus) {
            $travelRequest->update(['status' => $newStatus]);

            // Notificação ao solicitante (queue)
            $travelRequest->notifyRequesterStatusChanged();
        });

        return $travelRequest;
    }
}
