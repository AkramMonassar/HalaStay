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
    public function __construct(protected AvailabilityService $availabilityService)
    {
    }

    public function createBooking(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {

            // 1) جلب نوع الإقامة مع قفل الصف لمنع التعارض (BR-06)
            $type = AccommodationType::whereKey($data['accommodation_type_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$type) {
                throw ValidationException::withMessages([
                    'accommodation_type_id' => 'نوع الإقامة غير موجود أو غير نشط.',
                ]);
            }

            // 2) التحقق من اعتماد الفندق ونشاطه (BR-07)
            $hotel = Hotel::whereKey($type->hotel_id)
                ->where('status', 'approved')
                ->where('is_active', true)
                ->first();

            if (!$hotel) {
                throw ValidationException::withMessages([
                    'accommodation_type_id' => 'الفندق غير متاح للحجز حالياً.',
                ]);
            }

            // 3) حساب عدد الليالي (BR-01 / BR-02)
            $checkIn = $data['check_in'];
            $checkOut = $data['check_out'];
            $nights = (int) Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

            if ($nights < 1) {
                throw ValidationException::withMessages([
                    'check_out' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول بليلة واحدة على الأقل.',
                ]);
            }

            // 4) قاعدة السعة (§4.8.4)
            $rooms = (int) $data['rooms'];
            $guests = (int) $data['adults'] + (int) ($data['children'] ?? 0);

            if ($guests > $rooms * ($type->max_adults + $type->max_children)) {
                throw ValidationException::withMessages([
                    'adults' => 'عدد الأفراد يتجاوز السعة المسموحة لهذا النوع من الإقامة.',
                ]);
            }

            // 5) التحقق من التوفر عبر خدمة التوفر الموحدة (BR-05)
            $availableUnits = $this->availabilityService->availableUnits($type, $checkIn, $checkOut);

            if ($availableUnits < $rooms) {
                throw ValidationException::withMessages([
                    'rooms' => 'عدد الغرف المطلوبة غير متاح في هذه التواريخ.',
                ]);
            }

            // 6) حساب السعر الإجمالي (BR-12)
            $totalPrice = $type->base_price * $nights * $rooms;

            // 7) إنشاء الحجز برقم فريد (BR-14)
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

            // 8) توثيق اللحظة في سجل حالات الحجز (§4.7.13)
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
}