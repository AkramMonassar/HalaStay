<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'amount' => $this->amount,
            'currency_code' => $this->currency_code,
            'payment_method' => $this->whenLoaded('paymentMethod', fn() => $this->paymentMethod->name),
            'payment_status' => $this->payment_status,
            'receipt_image' => $this->receipt_image ? asset('storage/' . $this->receipt_image) : null,
            'paid_at' => $this->paid_at,
            'booking_number' => $this->whenLoaded('booking', fn() => $this->booking->booking_number),
            'hotel_name' => $this->whenLoaded('booking', fn() => $this->booking->hotel?->name),
            'guest_name' => $this->whenLoaded('user', fn() => $this->user->name),
            'created_at' => $this->created_at,
        ];
    }
}
