<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Casts\UuidV7Cast;
use App\Support\Generators\ShortCodeGenerator;
use Ramsey\Uuid\Uuid;

trait HasBinaryUuidV7PrimaryKey
{
    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * The data type of the primary key ID.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    abstract protected function codePrefix(): string;

    protected static function bootHasBinaryUuidV7PrimaryKey(): void
    {
        static::creating(function ($model) {
            $uuid = Uuid::uuid7();

            $model->{$model->getKeyName()} = $uuid->getBytes();

            $model->code = app(ShortCodeGenerator::class)
                            // Remove todos os - do final (se houver) e adiciona apenas um -
                ->withPrefix(rtrim($model->codePrefix(), '-').'-')
                ->generate($uuid->toString());
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            $this->getKeyName() => UuidV7Cast::class,
        ];
    }
}
