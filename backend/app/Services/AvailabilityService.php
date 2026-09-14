<?php

namespace App\Services;

use App\Models\AccommodationType;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /** حالات الحجز التي تحجز الوحدات فعلياً — تشمل بانتظار الدفع لحيازة الوحدات خلال نافذة الدفع */
    public const BLOCKING_STATUSES = ['pending_payment', 'pending_confirmation', 'confirmed', 'completed'];

    /** عدد الوحدات المحجوزة لنوع إقامة بين تاريخين */
    public function bookedUnits(int $accommodationTypeId, string $checkIn, string $checkOut): int
    {
        return (int) Booking::query()
            ->where('accommodation_type_id', $accommodationTypeId)
            ->whereIn('booking_status', self::BLOCKING_STATUSES)
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->sum('rooms_count');
    }

    /** الوحدات المتبقية لنوع إقامة في فترة معينة */
    public function availableUnits(AccommodationType $type, string $checkIn, string $checkOut): int
    {
        return max(0, $type->total_units - $this->bookedUnits($type->id, $checkIn, $checkOut));
    }

    /** أنواع الإقامة النشطة لفندق مع available_units مصفاة حسب الغرف المطلوبة والسعة */
    public function typesForHotel(Hotel $hotel, string $checkIn, string $checkOut, int $rooms = 1, int $guests = 0): Collection
    {
        return $hotel->accommodationTypes()
            ->where('is_active', true)
            ->get()
            ->map(function (AccommodationType $type) use ($checkIn, $checkOut) {
                $type->available_units = $this->availableUnits($type, $checkIn, $checkOut);
                return $type;
            })
            ->filter(fn (AccommodationType $type) => $type->available_units >= $rooms)
            ->filter(fn (AccommodationType $type) => $guests === 0 || $rooms * ($type->max_adults + $type->max_children) >= $guests)
            ->values();
    }
}