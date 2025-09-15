<?php

declare(strict_types=1);

namespace App\Support\Outbox;

use InvalidArgumentException;
use Throwable;

class InvalidPayloadException extends InvalidArgumentException
{
    /**
     * Create a new exception instance.
     */
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        parent::__construct(message: $message, previous: $previous);
    }
}
