<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use App\Models\OutboxMessage;
use App\Support\Outbox\Outbox;
use Symfony\Component\Uid\Uuid;
use Illuminate\Support\Collection;
use App\Support\Outbox\OutboxMessageEvent;
use Illuminate\Contracts\Support\Arrayable;
use App\Support\Outbox\OutboxAggregateContract;

if (! function_exists('outbox')) {
    function outbox(
        Closure $closure,
        OutboxMessageEvent $event,
        ?OutboxAggregateContract $aggregate = null
    ): OutboxMessage|true {
        return app(Outbox::class)->execute($closure, $event, $aggregate);
    }
}

if (! function_exists('convertUuidToBinary')) {
    function convertUuidToBinary(mixed $ids): mixed
    {
        $ids = match (true) {
            is_array($ids) => array_map('convertUuidToBinary', $ids),
            $ids instanceof Collection => $ids->map('convertUuidToBinary')->all(),
            $ids instanceof Arrayable => array_map('convertUuidToBinary', $ids->toArray()),
            default => $ids,
        };

        if (is_string($ids) && Str::isUuid($ids)) {
            return Uuid::fromString($ids)->toBinary();
        }

        return $ids;
    }
}
