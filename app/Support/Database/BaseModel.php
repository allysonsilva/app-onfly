<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasBinaryUuidV7PrimaryKey;
use Illuminate\Support\Collection;

class BaseModel extends Model
{
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'id',
    ];

    /**
     * Get a new query to restore one or more models by their queueable IDs.
     *
     * Esse método é utilizado pelo worker do Laravel para restaurar modelos
     * que estão na fila e que precisam ser processados, e nesse caso, o ID
     * do modelo que está na fila, está como string (UUIDv7) e não como
     * binário (UUIDv7 em binário), assim, é necessário converter para binário!
     *
     * @param array|int|string|Collection|Arrayable $ids
     */
    public function newQueryForRestoration($ids)
    {
        if (isset(class_uses_recursive($this)[HasBinaryUuidV7PrimaryKey::class])) {
            $ids = convertUuidToBinary($ids);
        }

        // @phpstan-ignore-next-line
        return parent::newQueryForRestoration($ids);
    }
}
