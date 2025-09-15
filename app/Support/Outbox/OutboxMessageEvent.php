<?php

declare(strict_types=1);

namespace App\Support\Outbox;

use App\Enums\Traits\HasEnumValues;

enum OutboxMessageEvent: string
{
    use HasEnumValues;

    case QUEUE_MAKE_SEARCHABLE = 'queue-make-searchable';
    case NOTIFY_REQUESTER_STATUS_CHANGED = 'notify-requester-status-changed';
}
