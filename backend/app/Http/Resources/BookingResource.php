<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'guest_name' => $this->whenLoaded('user', fn () => $this->user->name),
            'hotel' => $this->whenLoaded('hotel', fn () => $this->hotel->name),
            'accommodation_type' => $this->whenLoaded('accommodationType', fn () => $this->accommodationType->name),
            'check_in' => $this->check_in->toDateString(),
            'check_out' => $this->check_out->toDateString(),
            'nights' => $this->nights,
            'adults' => $this->adults,
            'children' => $this->children,
            'rooms_count' => $this->rooms_count,
            'total_price' => $this->total_price,
            'currency_code' => $this->currency_code,
            'booking_status' => $this->booking_status,
            'payment_status' => $this->payment_status,
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'status_history' => BookingStatusHistoryResource::collection($this->whenLoaded('statusHistory')),
            'created_at' => $this->created_at,
        ];
    }
}