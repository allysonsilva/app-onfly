<?php

declare(strict_types=1);

namespace App\Support\Outbox;

use App\Enums\Traits\HasEnumValues;

enum OutboxMessageStatus: string
{
    use HasEnumValues;

    case PENDING = 'pending';

    case PROCESSING = 'processing';

    case DONE = 'done';

    case FAILED = 'failed';
}
