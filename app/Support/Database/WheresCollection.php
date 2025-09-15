<?php

declare(strict_types=1);

namespace App\Support\Database;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use JsonSerializable;
use Symfony\Component\Uid\Uuid;

class WheresCollection implements Arrayable, ArrayAccess, Countable, JsonSerializable
{
    public function __construct(private array $data = [])
    {
    }

    /**
     * Determine if the given offset exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * Get the value at the given offset.
     */
    public function offsetGet(mixed $offset): string
    {
        return $this->data[$offset];
    }

    /**
     * Set the value at the given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $offsetStored = $this->storeDataItem($offset, $value);

        // Trabalhe sobre REFERÊNCIA ao item recém armazenado ou recuperado
        // Para utilização no foreach abaixo setando a chave de `$item['value']`
        $item = &$this->data[$offsetStored];

        // Se não for array ou não for a coluna `id` no `where`, retorna!
        if (! is_array($item) || ($item['column'] ?? null) !== 'id') {
            return;
        }

        foreach (Arr::wrap($item['value'] ?? null) as $uuid) {
            if ($uuid instanceof Expression || (strlen((string) $uuid) !== 36 || ! Uuid::isValid((string) $uuid))) {
                continue;
            }

            $item['value'] = Uuid::fromString($uuid)->toBinary();
        }
    }

    /**
     * Unset the value at the given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get the array that should be JSON serialized.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function storeDataItem(mixed $offset, mixed $value): mixed
    {
        // Atribui e descobre a chave real quando offset é null
        if (empty($offset)) {
            $this->data[] = $value;
            end($this->data); // Move o ponteiro para o último
            $offset = key($this->data); // Pega a chave recém-criada
        } else {
            $this->data[$offset] = $value;
        }

        return $offset;
    }
}
