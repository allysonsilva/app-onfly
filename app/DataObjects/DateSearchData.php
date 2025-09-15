<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Enums\FilterOperator;
use App\Support\Data\BaseDataResource;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class DateSearchData extends BaseDataResource
{
    public function __construct(
        public readonly ?FilterOperator $operator,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon $date,
    ) {
        $this->date = $date->startOfDay();
    }

    public function toTypesense(): string
    {
        $timestamp = $this->date->getTimestamp();

        // Remove '=' from the operator for Typesense compatibility
        return ltrim("{$this->operator?->value}{$timestamp}", '=');
    }
}
