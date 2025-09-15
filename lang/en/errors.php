<?php

declare(strict_types=1);

return [
    'travel_request_already_in_status' => [
        'title' => 'Status Already Set',
        'detail' => 'The travel request is already in the requested status, no update was performed.',
    ],
    'travel_request_already_cancelled' => [
        'title' => 'Travel Request Already Cancelled',
        'detail' => 'Cannot cancel a cancelled travel request.',
    ],
    'travel_request_not_cancellable' => [
        'title' => 'Unable to Cancel Travel Request',
        'detail' => 'The deadline for canceling this travel request has passed. You can no longer cancel this trip.',
    ],
];
