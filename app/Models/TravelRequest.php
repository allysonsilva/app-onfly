<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\UuidV7Cast;
use App\DataObjects\RequesterData;
use App\Enums\TravelRequestStatus;
use App\Models\Concerns\HandlesLoggedUser;
use App\Models\Concerns\HasOutboxAggregate;
use App\Notifications\TravelRequestStatusChanged;
use App\Observers\TravelRequestObserver;
use App\Support\Database\BaseModel;
use App\Support\Outbox\OutboxAggregateContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use App\Support\Outbox\OutboxMessageEvent;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\Scout;

/**
 * @property string $id
 * @property string $code
 * @property int $user_id
 * @property RequesterData $requester
 * @property string $destination
 * @property \Illuminate\Support\Carbon $departure_date
 * @property \Illuminate\Support\Carbon $return_date
 * @property TravelRequestStatus $status
 * @property \Illuminate\Support\Carbon|null $canceled_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read User $user
 */
#[ObservedBy([TravelRequestObserver::class])]
class TravelRequest extends BaseModel implements OutboxAggregateContract
{
    use HandlesLoggedUser;
    use HasOutboxAggregate;
    use Searchable;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'requester',
        'destination',
        'departure_date',
        'return_date',
        'status',
        'canceled_at',
        'approved_at',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'destination' => $this->destination,
            'departure_date' => $this->departure_date->timestamp,
            'return_date' => $this->return_date->timestamp,
            'status' => $this->status->value,
            'created_at' => $this->created_at->timestamp,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @inheritDoc
     *
     * Substituído o método original para tratar problemas com conexões,
     * garantindo resiliência com pattern de outbox.
     *
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     */
    public function queueMakeSearchable($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        outbox(function () use ($models) {
            dispatch((new Scout::$makeSearchableJob($models))
                ->onQueue($models->first()->syncWithSearchUsingQueue())
                ->onConnection($models->first()->syncWithSearchUsing()));
        }, OutboxMessageEvent::QUEUE_MAKE_SEARCHABLE, $models->first());
    }

    /**
     * Converte os IDs vindos do Scout (string) para binário antes do whereIn.
     */
    public function queryScoutModelsByIds(ScoutBuilder $builder, array $ids)
    {
        $query = $this->newQuery();

        if ($builder->queryCallback) {
            call_user_func($builder->queryCallback, $query);
        }

        return $query->whereIn(
            $this->qualifyColumn($this->getScoutKeyName()),
            convertUuidToBinary($ids)
        );
    }

    public function notifyRequesterStatusChanged(): void
    {
        outbox(
            fn () => $this->user->notify(new TravelRequestStatusChanged($this)),
            OutboxMessageEvent::NOTIFY_REQUESTER_STATUS_CHANGED,
            $this
        );
    }

    /**
     * Get the code prefix for the model.
     */
    protected function codePrefix(): string
    {
        return 'TVR';
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
            'requester' => RequesterData::class,
            'status' => TravelRequestStatus::class,
            'departure_date' => 'datetime:Y-m-d',
            'return_date' => 'datetime:Y-m-d',
            'canceled_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }
}
