<?php

declare(strict_types=1);

namespace App\Errors;

use App\Support\Errors\HttpErrorStatus;
use App\Support\Errors\ErrorDetail;

class TravelRequestAlreadyCancelled extends ErrorDetail
{
    public function __construct()
    {
        parent::__construct(
            title: __('errors.travel_request_already_cancelled.title'),
            detail: __('errors.travel_request_already_cancelled.detail'),
            errorCode: 'travel-request-already-cancelled',
            httpStatus: HttpErrorStatus::BAD_REQUEST,
        );
    }
}
