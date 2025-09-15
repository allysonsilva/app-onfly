<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Models\User;
use App\Support\Data\BaseDataResource;

final class RequesterData extends BaseDataResource
{
    public function __construct(
        public string $name,
        public string $email,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->name,
            $user->email,
        );
    }
}
