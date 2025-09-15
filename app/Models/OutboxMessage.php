<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Outbox\OutboxMessageEvent;
use App\Support\Outbox\OutboxMessageStatus;

/**
 * @property int $id
 * @property OutboxMessageEvent $event
 * @property array $payload
 * @property OutboxMessageStatus $status
 * @property string|null $aggregate_type
 * @property string|null $aggregate_id
 * @property int $attempts
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class OutboxMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event',
        'payload',
        'status',
        'aggregate_type',
        'aggregate_id',
        'attempts',
        'processed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'json:unicode',
            'type' => OutboxMessageEvent::class,
            'status' => OutboxMessageStatus::class,
            'processed_at' => 'datetime',
        ];
    }
}
