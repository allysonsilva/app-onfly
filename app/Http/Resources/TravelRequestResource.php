<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\TravelRequest
 */
class TravelRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'requester' => $this->requester,
            'destination' => $this->destination,
            'departure_date' => $this->departure_date,
            'return_date' => $this->return_date,
            'status' => $this->status,
        ];
    }
}
