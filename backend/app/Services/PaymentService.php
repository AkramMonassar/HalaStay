<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /** حالات الحجز التي تسمح بإنشاء دفعة */
    public const PAYABLE_BOOKING_STATUSES = ['pending_payment'];

    /** حالات الدفعة التي تسمح برفع إشعار */
    public const RECEIPT_ALLOWED_STATUSES = ['pending', 'under_review'];

    public function createManualPayment(User $user, array $data, ?UploadedFile $receipt = null): Payment
    {
        return DB::transaction(function () use ($user, $data, $receipt) {

            $booking = Booking::whereKey($data['booking_id'])->lockForUpdate()->first();

            if (!$booking) {
                throw ValidationException::withMessages([
                    'booking_id' => 'الحجز غير موجود.',
                ]);
            }

            if ($booking->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'booking_id' => 'لا يمكنك إنشاء دفعة لحجز لا تملكه.',
                ]);
            }

            if (!in_array($booking->booking_status, self::PAYABLE_BOOKING_STATUSES, true)) {
                throw ValidationException::withMessages([
                    'booking_id' => 'لا يمكن إنشاء دفعة لحجز بحالة ' . $booking->booking_status . '.',
                ]);
            }

            $hasActivePayment = $booking->payments()
                ->whereIn('payment_status', ['pending', 'under_review'])
                ->exists();

            if ($hasActivePayment) {
                throw ValidationException::withMessages([
                    'booking_id' => 'توجد دفعة قيد المراجعة لهذا الحجز بالفعل.',
                ]);
            }

            $method = PaymentMethod::whereKey($data['payment_method_id'])
                ->where('is_active', true)
                ->where('type', 'manual')
                ->first();

            if (!$method) {
                throw ValidationException::withMessages([
                    'payment_method_id' => 'طريقة الدفع غير متاحة حالياً.',
                ]);
            }

            $isCash = $method->method_key === 'cash_on_arrival';
            $paymentStatus = $isCash ? 'pending' : 'under_review';

            $payment = Payment::create([
                'payment_number' => 'PAY-' . strtoupper(uniqid()),
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'payment_method_id' => $method->id,
                'amount' => $booking->total_price,
                'currency_code' => $booking->currency_code,
                'payment_gateway' => null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_status' => $paymentStatus,
                'receipt_image' => $receipt?->store('receipts', 'public'),
            ]);

            $oldStatus = $booking->booking_status;

            $booking->update([
                'booking_status' => 'pending_confirmation',
                'payment_status' => $paymentStatus,
            ]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => 'pending_confirmation',
                'note' => $isCash
                    ? 'تم اختيار الدفع عند الوصول'
                    : 'تم إنشاء دفعة يدوية بانتظار المراجعة',
            ]);

            return $payment;
        });
    }

    public function uploadReceipt(User $user, Payment $payment, UploadedFile $file): Payment
    {
        if ($payment->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'receipt' => 'لا يمكنك رفع إشعار لدفعة لا تملكها.',
            ]);
        }

        if (!in_array($payment->payment_status, self::RECEIPT_ALLOWED_STATUSES, true)) {
            throw ValidationException::withMessages([
                'receipt' => 'لا يمكن رفع إشعار لدفعة بحالة ' . $payment->payment_status . '.',
            ]);
        }

        return DB::transaction(function () use ($user, $payment, $file) {

            $payment->update([
                'receipt_image' => $file->store('receipts', 'public'),
                'payment_status' => 'under_review',
            ]);

            $booking = $payment->booking;

            if ($booking->payment_status !== 'under_review') {
                $oldStatus = $booking->booking_status;

                $booking->update([
                    'payment_status' => 'under_review',
                    'booking_status' => 'pending_confirmation',
                ]);

                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'changed_by' => $user->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'pending_confirmation',
                    'note' => 'تم رفع إشعار الدفع',
                ]);
            }

            return $payment->refresh();
        });
    }

    /** مراجعة صاحب الفندق لدفعة يدوية: اعتماد أو رفض (BR-10) */
    public function reviewPaymentByOwner(User $owner, Payment $payment, string $action, ?string $note = null): Payment
    {
        $payment->loadMissing('booking.hotel');

        if ($payment->booking?->hotel?->owner_id !== $owner->id) {
            throw ValidationException::withMessages([
                'payment' => 'لا يمكنك مراجعة دفعة لفندق لا تملكه.',
            ]);
        }

        if ($payment->payment_status !== 'under_review') {
            throw ValidationException::withMessages([
                'payment_status' => 'لا يمكن مراجعة دفعة بحالة ' . $payment->payment_status . '.',
            ]);
        }

        return DB::transaction(function () use ($owner, $payment, $action, $note) {
            $booking = $payment->booking;
            $oldBookingStatus = $booking->booking_status;

            if ($action === 'approve') {
                $payment->update([
                    'payment_status' => 'success',
                    'paid_at' => now(),
                    'admin_note' => $note,
                ]);

                $booking->update([
                    'payment_status' => 'paid',
                    'booking_status' => 'confirmed',
                ]);

                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'changed_by' => $owner->id,
                    'old_status' => $oldBookingStatus,
                    'new_status' => 'confirmed',
                    'note' => 'تم اعتماد إشعار الدفع من صاحب الفندق',
                ]);
                
                NotificationService::send($booking->user, 'تم اعتماد دفعتك', 'دفعتك للحجز ' . $booking->booking_number . ' معتمدة والحجز مؤكد.', 'payment');
            } else {
                $payment->update([
                    'payment_status' => 'failed',
                    'admin_note' => $note,
                ]);

                $booking->update([
                    'payment_status' => 'unpaid',
                    'booking_status' => 'pending_payment',
                ]);

                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'changed_by' => $owner->id,
                    'old_status' => $oldBookingStatus,
                    'new_status' => 'pending_payment',
                    'note' => 'تم رفض إشعار الدفع من صاحب الفندق' . ($note ? ': ' . $note : ''),
                ]);
                
                NotificationService::send($booking->user, 'تحديث على دفعتك', 'دفعتك للحجز ' . $booking->booking_number . ' مرفوضة. يمكنك إعادة المحاولة.', 'payment');
            }

            return $payment->refresh();
        });
    }
}