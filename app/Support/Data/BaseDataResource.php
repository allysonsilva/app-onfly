<?php

declare(strict_types=1);

namespace App\Support\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Resource;

#[MapName(SnakeCaseMapper::class)]
abstract class BaseDataResource extends Resource
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
