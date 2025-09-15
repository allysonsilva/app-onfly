<?php

declare(strict_types=1);

namespace App\Support\Outbox;

use App\Models\OutboxMessage;
use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionFunction;
use Throwable;

final class Outbox
{
    public function execute(
        Closure $closure,
        OutboxMessageEvent $event,
        ?OutboxAggregateContract $aggregate = null
    ): OutboxMessage|true {
        if ($this->tryExecuteFirst($closure)) {
            return true;
        }

        try {
            $payload = json_encode([
                'command' => serialize(new SerializableClosure($closure)),
                'displayName' => $this->displayName($closure, $event),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (Throwable $th) {
            throw new InvalidPayloadException(
                message: sprintf(
                    'Unable to JSON encode payload or failed to serialize closure of type [%s]: %s',
                    $event->value,
                    $th->getMessage()
                ),
                previous: $th
            );
        }

        return OutboxMessage::create([
            'event' => $event,
            'payload' => $payload,
            'status' => OutboxMessageStatus::PENDING,
            'aggregate_type' => $aggregate?->getAggregateType(),
            'aggregate_id' => $aggregate?->getAggregateId(),
        ]);
    }

    /**
     * Get the display name for the outbox.
     */
    public function displayName(Closure $closure, OutboxMessageEvent $event): string
    {
        $reflection = new ReflectionFunction($closure);

        return "{$event->value} - " .
            'Closure ('.basename($reflection->getFileName()).':'.$reflection->getStartLine().')';
    }

    private function tryExecuteFirst(Closure $closure): bool
    {
        try {
            app()->call($closure);
        } catch (Throwable) {
            return false;
        }

        return true;
    }
}
