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

    /** الحالات التي يسمح لصاحب الفندق بتأكيدها أو رفضها */
    public const OWNER_PROCESSABLE = ['pending_confirmation'];

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
            if ($hotel->owner_id === $user->id) {
            throw ValidationException::withMessages([
                'accommodation_type_id' => 'لا يمكن إنشاء حجز في فندق تملكه.',
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

    public function canCancel(Booking $booking): bool
    {
        return in_array($booking->booking_status, self::CANCELABLE_STATUSES, true);
    }

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

    /** تأكيد الحجز من صاحب الفندق */
    public function confirmBookingByOwner(User $owner, Booking $booking, ?string $note = null): Booking
    {
        $this->assertOwnerOfBookingHotel($owner, $booking);

        if (!in_array($booking->booking_status, self::OWNER_PROCESSABLE, true)) {
            throw ValidationException::withMessages([
                'booking_status' => 'لا يمكن تأكيد حجز بحالة ' . $booking->booking_status . '.',
            ]);
        }

        return DB::transaction(function () use ($owner, $booking, $note) {
            $oldStatus = $booking->booking_status;

            $booking->update(['booking_status' => 'confirmed']);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => $owner->id,
                'old_status' => $oldStatus,
                'new_status' => 'confirmed',
                'note' => $note ?? 'تم تأكيد الحجز من قبل صاحب الفندق',
            ]);
            NotificationService::send($booking->user, 'تم تأكيد حجزك', 'حجزك رقم ' . $booking->booking_number . ' مؤكد الآن. نتمنى لك إقامة سعيدة!', 'booking');
            return $booking->refresh();
        });
    }

    /** رفض الحجز من صاحب الفندق مع مزامنة الدفعة */
    public function rejectBookingByOwner(User $owner, Booking $booking, ?string $reason = null): Booking
    {
        $this->assertOwnerOfBookingHotel($owner, $booking);

        if (!in_array($booking->booking_status, self::OWNER_PROCESSABLE, true)) {
            throw ValidationException::withMessages([
                'booking_status' => 'لا يمكن رفض حجز بحالة ' . $booking->booking_status . '.',
            ]);
        }

        return DB::transaction(function () use ($owner, $booking, $reason) {
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
                'changed_by' => $owner->id,
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
                'note' => $reason ?? 'تم رفض الحجز من قبل صاحب الفندق',
            ]);
            NotificationService::send($booking->user, 'تحديث على حجزك', 'حجزك رقم ' . $booking->booking_number . ' مرفوض من قبل الفندق.', 'booking');
            return $booking->refresh();
        });
    }

    /** حماية الملكية: لا يدير صاحب الفندق إلا حجوزات فنادقه (BR-08) */
    protected function assertOwnerOfBookingHotel(User $user, Booking $booking): void
    {
        $isOwner = Hotel::whereKey($booking->hotel_id)
            ->where('owner_id', $user->id)
            ->exists();

        if (!$isOwner) {
            throw ValidationException::withMessages([
                'booking' => 'لا يمكنك إدارة حجوزات فندق لا تملكه.',
            ]);
        }
        
        if ($user->role === 'admin') {
            throw ValidationException::withMessages([
                'accommodation_type_id' => 'الحسابات الإدارية تستعرض المنصة ولا تنشئ حجوزات.',
            ]);
        }
    }
}