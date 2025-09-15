<?php

declare(strict_types=1);

namespace App\Enums\Traits;

trait HasEnumValues
{
    /**
     * Retrieve array with all values contained in this enum
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_combine(self::values(), self::values());
    }

    public static function except(array $keys): array
    {
        return array_filter(self::values(), fn ($value) => ! in_array($value, $keys));
    }
}
