<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    use ApiResponse;

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        if ($booking->user_id !== $request->user()->id) {
            return $this->errorResponse('This booking is not yours.', 403);
        }

        if ($booking->booking_status !== 'pending_payment') {
            return $this->errorResponse('The booking is not in "Awaiting Payment" status — a new payment cannot be created.', 422);
        }

        $method = PaymentMethod::findOrFail($validated['payment_method_id']);

        if (! $method->is_active) {
            return $this->errorResponse('This payment method is not available right now.', 422);
        }

        // القاعدة الفاشلة-بأمان: الإشعار إلزامي ما لم تصرّح الطريقة بعكسه صراحة
        $request->validate([
            'receipt_image' => $method->requires_receipt === false
                ? ['nullable', 'image', 'max:5120']
                : ['required', 'image', 'max:5120'],
        ]);

        $payment = DB::transaction(function () use ($request, $booking, $method) {
            $path = null;
            if ($request->hasFile('receipt_image')) {
                $path = $request->file('receipt_image')->store('receipts', 'public');
            }

            $payment = Payment::create([
                'user_id' => $request->user()->id,
                'booking_id' => $booking->id,
                'payment_method_id' => $method->id,
                'amount' => $booking->total_price,
                'currency_code' => $booking->currency_code,
                'payment_number' => 'PAY-' . strtoupper(Str::random(12)),
                'payment_status' => $method->requires_receipt === false ? 'pending' : 'under_review',
                'receipt_image' => $path,
            ]);

            DB::table('booking_status_history')->insert([
                'booking_id' => $booking->id,
                'changed_by' => $request->user()->id,
                'old_status' => 'pending_payment',
                'new_status' => 'pending_confirmation',
                'note' => $method->requires_receipt === false
                    ? 'Cash-on-arrival booking — awaiting owner confirmation.'
                    : 'Payment receipt uploaded — awaiting owner review.',
                'created_at' => now(),
            ]);

            $booking->update(['booking_status' => 'pending_confirmation']);

            $owner = $booking->accommodationType?->hotel?->owner;
            if ($owner) {
                Notification::create([
                    'user_id' => $owner->id,
                    'type' => 'payment_submitted',
                    'title' => 'A new payment awaits your action',
                    'body' => "Booking {$booking->booking_number} — {$method->name}.",
                ]);
            }

            return $payment;
        });

        return $this->successResponse(new PaymentResource($payment), 'Payment created successfully.', 201);
    }
}