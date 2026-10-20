<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'city_id' => $this->city_id,
            'name' => (app()->getLocale() === 'en' && $this->name_en ? $this->name_en : $this->name),
            'name_ar' => $this->name,
            'name_en' => $this->name_en,
            'description_ar' => $this->description,
            'description_en' => $this->description_en,
            'slug' => $this->slug,
            'description' => (app()->getlocale() === 'en' && $this->description_en ? $this->description_en : $this->description),
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'star_rating' => $this->star_rating,
            'review_score' => $this->review_score,
            'status' => $this->status,
            'owner_name' => $this->whenLoaded('owner', fn() => $this->owner->name),
            'city' => $this->whenLoaded('city', fn() => (app()->getLocale() === 'en' && $this->city->name_en ? $this->city->name_en : $this->city->name)),
            'images' => HotelImageResource::collection($this->whenLoaded('images')),
            'accommodation_types' => AccommodationTypeResource::collection($this->whenLoaded('accommodationTypes')),
        ];
    }
}
