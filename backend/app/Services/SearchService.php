<?php

namespace App\Services;

use App\Models\Hotel;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    public function __construct(protected AvailabilityService $availabilityService)
    {
    }

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
                            ->whereIn('bookings.booking_status', AvailabilityService::BLOCKING_STATUSES)
                            ->where('bookings.check_in', '<', $checkOut)
                            ->where('bookings.check_out', '>', $checkIn)
                            ->groupBy('bookings.accommodation_type_id')
                            ->havingRaw('SUM(bookings.rooms_count) > (accommodation_types.total_units - ?)', [$rooms]);
                    });
            })
            ->paginate(10)
            ->withQueryString();
    }

    /** أنواع الإقامة المتاحة لفندق محدد — تفويض كامل لخدمة التوفر */
    public function availableTypesFor(Hotel $hotel, string $checkIn, string $checkOut, int $rooms)
    {
        return $this->availabilityService->typesForHotel($hotel, $checkIn, $checkOut, $rooms);
    }
}