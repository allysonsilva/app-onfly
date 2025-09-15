<?php

declare(strict_types=1);

namespace App\Support\Errors;

use Exception;
use Illuminate\Http\JsonResponse;

class ErrorDetail extends Exception
{
    private array $additional = [];

    public function __construct(
        public readonly string $title,
        public readonly string $detail,
        public readonly string $errorCode,
        public readonly HttpErrorStatus $httpStatus,
    ) {
        parent::__construct(
            $title,
            $httpStatus->value
        );
    }

    public function report(): ?bool
    {
        return true;
    }

    public function withContext(array $additional): static
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Get the exception's context information.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->additional ?: [];
    }

    public function render(): JsonResponse
    {
        return response()->json(array_filter([
            'title' => $this->title,
            'detail' => $this->detail,
            'code' => lcfirst($this->errorCode),
            'status' => $this->httpStatus,
        ]), $this->httpStatus->value)->withHeaders([
            'Content-Type' => 'application/problem+json',
        ]);
    }
}
