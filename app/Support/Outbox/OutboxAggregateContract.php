<?php

declare(strict_types=1);

namespace App\Support\Outbox;

interface OutboxAggregateContract
{
    public function getAggregateId(): string;

    public function getAggregateType(): string;
}
