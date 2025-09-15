<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\HasEnumValues;

enum SortDirection: string
{
    use HasEnumValues;

    case ASC = 'asc';
    case DESC = 'desc';
}
