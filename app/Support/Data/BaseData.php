<?php

declare(strict_types=1);

namespace App\Support\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
abstract class BaseData extends Data
{
    /**
     * Returns only the fields that were/are filled with some information.
     * Returns only fields that are filled in, other than null.
     */
    public function onlyFilled(): array
    {
        return array_filter($this->toArray(), fn ($value) => ! is_null($value));
    }
}
