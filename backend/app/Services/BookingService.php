<?php

namespace App\Services;

use App\Models\AccommodationType;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Hotel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /** حالات الحجز التي يسمح فيها بالإلغاء (BR-15) */
    public const CANCELABLE_STATUSES = ['pending_payment', 'pending_confirmation'];

    public function __construct(protected AvailabilityService $availabilityService)
    {
    }

    public function createBooking(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {

            $type = AccommodationType::whereKey($data['accommodation_type_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$type) {
                throw ValidationException::withMessages([
                    'accommodation_type_id' => 'نوع الإقامة غير موجود أو غير نشط.',
                ]);
            }

            $hotel = Hotel::whereKey($type->hotel_id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->first();

            if (!$hotel) {
                throw ValidationException::withMessages([
                    'accommodation_type_id' => 'الفندق غير متاح للحجز حالياً.',
                ]);
            }

            $checkIn = $data['check_in'];
            $checkOut = $data['check_out'];
            $nights = (int) Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

            if ($nights < 1) {
                throw ValidationException::withMessages([
                    'check_out' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول بليلة واحدة على الأقل.',
                ]);
            }

            $rooms = (int) $data['rooms'];
            $guests = (int) $data['adults'] + (int) ($data['children'] ?? 0);

            if ($guests > $rooms * ($type->max_adults + $type->max_children)) {
                throw ValidationException::withMessages([
                    'adults' => 'عدد الأفراد يتجاوز السعة المسموحة لهذا النوع من الإقامة.',
                ]);
            }

            $availableUnits = $this->availabilityService->availableUnits($type, $checkIn, $checkOut);

            if ($availableUnits < $rooms) {
                throw ValidationException::withMessages([
                    'rooms' => 'عدد الغرف المطلوبة غير متاح في هذه التواريخ.',
                ]);
            }

            $totalPrice = $type->base_price * $nights * $rooms;

            $booking = Booking::create([
                'booking_number' => 'HS-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'hotel_id' => $hotel->id,
                'accommodation_type_id' => $type->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => $nights,
                'adults' => $data['adults'],
                'children' => $data['children'] ?? 0,
                'rooms_count' => $rooms,
                'total_price' => $totalPrice,
                'currency_code' => $type->currency_code,
                'booking_status' => 'pending_payment',
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $user->id,
                'old_status' => null,
                'new_status' => 'pending_payment',
                'note' => 'تم إنشاء الحجز',
            ]);

            return $booking;
        });
    }

    /** هل يمكن إلغاء هذا الحجز وفق السياسة؟ */
    public function canCancel(Booking $booking): bool
    {
        return in_array($booking->booking_status, self::CANCELABLE_STATUSES, true);
    }

    /** إلغاء الحجز مع مزامنة حالة الدفع وتسجيل الحدث (BR-13 / BR-15) */
    public function cancelBooking(User $user, Booking $booking, ?string $reason = null): Booking
    {
        if ($booking->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'booking' => 'لا يمكنك إلغاء حجز لا تملكه.',
            ]);
        }

        if (!$this->canCancel($booking)) {
            throw ValidationException::withMessages([
                'booking_status' => 'لا يمكن إلغاء الحجز في حالته الحالية (' . $booking->booking_status . ').',
            ]);
        }

        return DB::transaction(function () use ($user, $booking, $reason) {
            $oldStatus = $booking->booking_status;

            $booking->update([
                'booking_status' => 'cancelled',
                'payment_status' => $booking->payment_status === 'under_review'
                    ? 'refunded'
                    : $booking->payment_status,
            ]);

            if ($booking->payment_status === 'refunded') {
                $booking->payments()
                    ->where('payment_status', 'under_review')
                    ->update(['payment_status' => 'refunded']);
            }

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
                'note' => $reason ?? 'تم إلغاء الحجز من قبل المستخدم',
            ]);

            return $booking->refresh();
        });
    }
}