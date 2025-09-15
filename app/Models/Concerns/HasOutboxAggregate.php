<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait HasOutboxAggregate
{
    public function getAggregateId(): string
    {
        return (string) $this->getKey();
    }

    public function getAggregateType(): string
    {
        return $this::class;
    }
}
