<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stay_type' => $this->stay_type,
            'base_price' => $this->base_price,
            'currency_code' => $this->currency_code,
            'max_adults' => $this->max_adults,
            'max_children' => $this->max_children,
            'available_units' => $this->when(isset($this->available_units), $this->available_units),
        ];
    }
}