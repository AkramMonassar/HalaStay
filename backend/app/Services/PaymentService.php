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

            // 1) قفل الحجز والتحقق من ملكيته وحالته
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

            // 2) منع تكرار الدفعات النشطة على نفس الحجز
            $hasActivePayment = $booking->payments()
                ->whereIn('payment_status', ['pending', 'under_review'])
                ->exists();

            if ($hasActivePayment) {
                throw ValidationException::withMessages([
                    'booking_id' => 'توجد دفعة قيد المراجعة لهذا الحجز بالفعل.',
                ]);
            }

            // 3) التحقق من طريقة الدفع (يدوية ونشطة فقط)
            $method = PaymentMethod::whereKey($data['payment_method_id'])
                ->where('is_active', true)
                ->where('type', 'manual')
                ->first();

            if (!$method) {
                throw ValidationException::withMessages([
                    'payment_method_id' => 'طريقة الدفع غير متاحة حالياً.',
                ]);
            }

            // 4) حالة الدفعة حسب الطريقة: وصول = pending | تحويل/محفظة = under_review
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

            // 5) تحديث الحجز وتوثيق الحدث (BR-13)
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

    /** رفع إشعار الدفع لدفعة موجودة وتحديث الحالات */
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
}