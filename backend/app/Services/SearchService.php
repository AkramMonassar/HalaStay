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
        $checkIn = $filters['check_in'] ?? null;
        $checkOut = $filters['check_out'] ?? null;
        $hasDates = ! empty($checkIn) && ! empty($checkOut);
        $rooms = (int) ($filters['rooms'] ?? 1);
        $guests = (int) ($filters['adults'] ?? 2) + (int) ($filters['children'] ?? 0);

        return Hotel::query()
            ->with(['city', 'images'])
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where('city_id', $filters['city_id'])
            ->when(! empty($filters['star_rating']), function ($query) use ($filters) {
                $query->whereIn('star_rating', $filters['star_rating']);
            })
            ->when(isset($filters['min_review']), function ($query) use ($filters) {
                $query->where('review_score', '>=', $filters['min_review']);
            })
            ->whereHas('accommodationTypes', function ($query) use ($filters, $hasDates, $checkIn, $checkOut, $rooms, $guests) {
                $query->where('is_active', true)
                    ->when(! empty($filters['stay_type']), function ($q) use ($filters) {
                        $q->whereIn('stay_type', $filters['stay_type']);
                    })
                    // وضع الحجز فقط: قاعدة السعة §4.8.4 واستبعاد الممتلئ §4.8.2
                    ->when($hasDates, function ($q) use ($checkIn, $checkOut, $rooms, $guests) {
                        $q->whereRaw('(? * (max_adults + max_children)) >= ?', [$rooms, $guests])
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
                    });
            })
            ->paginate(10)
            ->withQueryString();
    }

    /** أنواع الإقامة المتاحة لفندق محدد — تفويض كامل لخدمة التوفر */
    public function availableTypesFor(Hotel $hotel, string $checkIn, string $checkOut, int $rooms, int $guests = 0)
    {
        return $this->availabilityService->typesForHotel($hotel, $checkIn, $checkOut, $rooms, $guests);
    }
}