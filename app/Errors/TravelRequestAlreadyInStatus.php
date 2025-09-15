<?php

declare(strict_types=1);

namespace App\Errors;

use App\Support\Errors\HttpErrorStatus;
use App\Support\Errors\ErrorDetail;

class TravelRequestAlreadyInStatus extends ErrorDetail
{
    public function __construct()
    {
        parent::__construct(
            title: __('errors.travel_request_already_in_status.title'),
            detail: __('errors.travel_request_already_in_status.detail'),
            errorCode: 'travel_request_already_in_status',
            httpStatus: HttpErrorStatus::BAD_REQUEST,
        );
    }
}
