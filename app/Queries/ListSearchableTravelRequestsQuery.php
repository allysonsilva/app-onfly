<?php

declare(strict_types=1);

namespace App\Queries;

use App\DataObjects\SearchData;
use App\Models\TravelRequest;
use App\Support\Contracts\QueryInterface;
use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Pipeline;
use Laravel\Scout\Builder as ScoutBuilder;

final class ListSearchableTravelRequestsQuery implements QueryInterface
{
    private SearchData $data;

    public function __construct(protected TravelRequest $entity)
    {
    }

    public function paginate(SearchData $data): Paginator
    {
        return $this->builder($data)->simplePaginate(perPage: $data->perPage);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, TravelRequest>
     */
    public function get(SearchData $data): Collection
    {
        return $this->builder($data)->get();
    }

    /**
     * @return ScoutBuilder<\App\Models\TravelRequest>
     */
    public function builder(SearchData $data): ScoutBuilder
    {
        $this->data = $data;

        /** @var \Laravel\Scout\Builder $scoutBuilder */
        $scoutBuilder = $this->entity
            ->search($data->destination)
            ->where('user_id', auth()->id());

        Pipeline::send($scoutBuilder)
            ->through([
                [$this, 'pipeFilterStatus'],
                [$this, 'pipeFilterDepartureDate'],
                [$this, 'pipeFilterReturnDate'],
            ])
            ->thenReturn();

        return $scoutBuilder->orderBy('created_at', $data->orderBy->value);
    }

    public function pipeFilterStatus(ScoutBuilder $scoutBuilder, Closure $next): ScoutBuilder
    {
        if (! empty($this->data->status)) {
            $scoutBuilder->query(function (EloquentBuilder $builder) {
                $builder->where('status', $this->data->status);
            });
        }

        return $next($scoutBuilder);
    }

    public function pipeFilterDepartureDate(ScoutBuilder $scoutBuilder, Closure $next): ScoutBuilder
    {
        if (! empty($this->data->departureDate)) {
            $scoutBuilder->where('departure_date', $this->data->departureDate->toTypesense());
        }

        return $next($scoutBuilder);
    }

    public function pipeFilterReturnDate(ScoutBuilder $scoutBuilder, Closure $next): ScoutBuilder
    {
        if (! empty($this->data->returnDate)) {
            $scoutBuilder->where('departure_date', $this->data->returnDate->toTypesense());
        }

        return $next($scoutBuilder);
    }
}
