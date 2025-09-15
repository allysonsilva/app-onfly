<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\UpdateTravelRequestStatusAction;
use App\Http\Requests\Admin\UpdateTravelRequestStatusRequest;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;

final class UpdateTravelRequestStatusController
{
    public function __construct(public UpdateTravelRequestStatusAction $action)
    {
    }

    /**
     * Handle an incoming authentication request.
     */
    public function __invoke(
        UpdateTravelRequestStatusRequest $request,
        TravelRequest $travelRequest
    ): TravelRequestResource {
        return new TravelRequestResource($this->action->handle($travelRequest, $request->status()));
    }
}
