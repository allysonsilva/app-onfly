<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TravelRequestStatus;
use App\Support\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateTravelRequestStatusRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(TravelRequestStatus::toHandle())],
        ];
    }

    public function status(): TravelRequestStatus
    {
        return TravelRequestStatus::from($this->input('status'));
    }
}
