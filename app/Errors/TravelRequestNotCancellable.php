<?php

declare(strict_types=1);

namespace App\Errors;

use App\Support\Errors\HttpErrorStatus;
use App\Support\Errors\ErrorDetail;

class TravelRequestNotCancellable extends ErrorDetail
{
    public function __construct()
    {
        parent::__construct(
            title: __('errors.travel_request_not_cancellable.title'),
            detail: __('errors.travel_request_not_cancellable.detail'),
            errorCode: 'travel-request-not-cancellable',
            httpStatus: HttpErrorStatus::FORBIDDEN,
        );
    }
}
