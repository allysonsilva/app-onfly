<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Queries\CanCancelTravelRequestQuery;
use App\Support\Actions\LoggedUserAction;
use Illuminate\Support\Facades\DB;

class CancelTravelRequestAction extends LoggedUserAction
{
    public function __construct(public CanCancelTravelRequestQuery $canCancelTravelRequestQuery)
    {
    }

    /**
     * Execute the Action.
     *
     * @param \App\Models\TravelRequest $travelRequest
     */
    public function handle(TravelRequest $travelRequest): void
    {
        $this->canCancelTravelRequestQuery->handle($travelRequest);

        DB::transaction(function () use ($travelRequest) {
            $travelRequest->update(['status' => TravelRequestStatus::CANCELED]);

            $travelRequest->notifyRequesterStatusChanged();
        });
    }
}
