<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => (app()->getLocale() === 'en' && $this->name_en ? $this->name_en : $this->name),
            'method_key' => $this->method_key,
            'type' => $this->type,
            'description' => (app()->getlocale() === 'en' && $this->description_en ? $this->description_en : $this->description),
            'is_active' => $this->is_active,
        ];
    }
}
