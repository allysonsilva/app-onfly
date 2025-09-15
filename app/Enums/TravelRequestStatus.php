<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\HasEnumValues;

enum TravelRequestStatus: string
{
    use HasEnumValues;

    case REQUESTED = 'requested';

    case APPROVED = 'approved';

    case CANCELED = 'canceled';

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isCanceled(): bool
    {
        return $this === self::CANCELED;
    }

    public static function toHandle(): array
    {
        return self::except([self::REQUESTED->value]);
    }
}
