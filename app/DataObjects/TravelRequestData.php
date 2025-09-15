<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Enums\TravelRequestStatus;
use App\Support\Data\BaseDataResource;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

final class TravelRequestData extends BaseDataResource
{
    public string $destination;

    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
    public Carbon $departureDate;

    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d')]
    public Carbon $returnDate;

    #[Computed]
    public RequesterData $requester;

    #[Computed]
    public TravelRequestStatus $status;

    public function __construct()
    {
        $this->requester = RequesterData::from(auth()->user());

        $this->status = TravelRequestStatus::REQUESTED;
    }
}
