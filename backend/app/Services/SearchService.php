<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    /** حالات الحجز التي تحجز الوحدات فعلياً */
    public const BLOCKING_STATUSES = ['pending_confirmation', 'confirmed', 'completed'];

    public function search(array $filters): LengthAwarePaginator
    {
        $checkIn = $filters['check_in'];
        $checkOut = $filters['check_out'];
        $rooms = (int) $filters['rooms'];
        $guests = (int) $filters['adults'] + (int) ($filters['children'] ?? 0);

        return Hotel::query()
            ->with(['city', 'images'])
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where('city_id', $filters['city_id'])
            ->when(!empty($filters['star_rating']), function ($query) use ($filters) {
                $query->whereIn('star_rating', $filters['star_rating']);
            })
            ->when(isset($filters['min_review']), function ($query) use ($filters) {
                $query->where('review_score', '>=', $filters['min_review']);
            })
            ->whereHas('accommodationTypes', function ($query) use ($filters, $checkIn, $checkOut, $rooms, $guests) {
                $query->where('is_active', true)
                    ->when(!empty($filters['stay_type']), function ($q) use ($filters) {
                        $q->whereIn('stay_type', $filters['stay_type']);
                    })
                    // قاعدة السعة §4.8.4: الأفراد <= الغرف × (max_adults + max_children)
                    ->whereRaw('(? * (max_adults + max_children)) >= ?', [$rooms, $guests])
                    // استبعاد الأنواع التي لا يتبقى فيها وحدات كافية §4.8.2
                    ->whereNotExists(function ($sub) use ($checkIn, $checkOut, $rooms) {
                        $sub->selectRaw('1')
                            ->from('bookings')
                            ->whereColumn('bookings.accommodation_type_id', 'accommodation_types.id')
                            ->whereIn('bookings.booking_status', self::BLOCKING_STATUSES)
                            ->where('bookings.check_in', '<', $checkOut)
                            ->where('bookings.check_out', '>', $checkIn)
                            ->groupBy('bookings.accommodation_type_id')
                            ->havingRaw('SUM(bookings.rooms_count) > (accommodation_types.total_units - ?)', [$rooms]);
                    });
            })
            ->paginate(10)
            ->withQueryString();
    }

    /** أنواع الإقامة المتاحة لفندق محدد مع عدد الوحدات المتبقية */
    public function availableTypesFor(Hotel $hotel, string $checkIn, string $checkOut, int $rooms)
    {
        $types = $hotel->accommodationTypes()->where('is_active', true)->get();

        $bookedMap = Booking::query()
            ->whereIn('accommodation_type_id', $types->pluck('id'))
            ->whereIn('booking_status', self::BLOCKING_STATUSES)
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->selectRaw('accommodation_type_id, SUM(rooms_count) as booked')
            ->groupBy('accommodation_type_id')
            ->pluck('booked', 'accommodation_type_id');

        return $types->map(function ($type) use ($bookedMap, $rooms) {
            $booked = (int) ($bookedMap[$type->id] ?? 0);
            $type->available_units = max(0, $type->total_units - $booked);
            return $type;
        })->filter(fn ($type) => $type->available_units >= $rooms)->values();
    }
}