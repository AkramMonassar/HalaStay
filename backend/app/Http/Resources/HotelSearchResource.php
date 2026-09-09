<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cover = $this->images->firstWhere('is_cover', true) ?? $this->images->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->whenLoaded('city', fn () => $this->city->name),
            'star_rating' => $this->star_rating,
            'review_score' => $this->review_score,
            'cover_image' => $cover?->image_path,
            'available_types' => AccommodationTypeResource::collection($this->available_types ?? collect()),
        ];
    }
}