<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DataObjects\SearchData;
use App\Enums\SortDirection;
use App\Enums\TravelRequestStatus;
use App\Rules\DateFilterExpression;
use App\Support\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Enum;

class IndexTravelRequestRequest extends BaseRequest
{
    public ?DateFilterExpression $departureDate;

    public ?DateFilterExpression $returnDate;

    public function __invoke(): SearchData
    {
        $data = array_filter(array_merge($this->validated(), [
            'departure_date' => $this->departureDate?->data(),
            'return_date' => $this->returnDate?->data(),
        ]));

        return SearchData::from($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order_by' => ['sometimes', 'required', new Enum(SortDirection::class)],
            'per_page' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', new Enum(TravelRequestStatus::class)],
            'destination' => ['sometimes', 'required', 'max:255'],
            'departure_date' => ['sometimes', 'required', $this->departureDate = new DateFilterExpression()],
            'return_date' => ['sometimes', 'required', $this->returnDate = new DateFilterExpression()],
        ];
    }
}
