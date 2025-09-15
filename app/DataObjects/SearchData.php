<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Enums\SortDirection;
use App\Enums\TravelRequestStatus;
use App\Support\Data\BaseDataResource;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class SearchData extends BaseDataResource implements Arrayable
{
    public function __construct(
        public ?SortDirection $orderBy,
        public ?int $perPage,
        public ?TravelRequestStatus $status,
        public ?string $destination,
        public ?DateSearchData $departureDate,
        public ?DateSearchData $returnDate,
    ) {
        $this->orderBy = $this->orderBy ?: SortDirection::DESC;
        $this->destination = $this->destination ?: '';
    }
}
