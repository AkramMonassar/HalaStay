<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerStatsController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request): JsonResponse
    {
        $hotelIds = $request->user()->ownedHotels()->pluck('id');

        $bookings = Booking::whereIn('hotel_id', $hotelIds)->get();

        $ownerPayments = Payment::whereHas('booking', fn ($q) => $q->whereIn('hotel_id', $hotelIds))->get();
        $success = $ownerPayments->where('payment_status', 'success');

        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('Y-m'));
        $revenueByMonth = $success->groupBy(fn ($p) => $p->updated_at->format('Y-m'));
        $bookingsByMonth = $bookings->groupBy(fn ($b) => $b->created_at->format('Y-m'));

        return $this->successResponse([
            'bookings_total' => $bookings->count(),
            'by_status' => $bookings->groupBy('booking_status')->map->count(),
            'pending_confirmation' => $bookings->where('booking_status', 'pending_confirmation')->count(),
            'under_review' => $ownerPayments->where('payment_status', 'under_review')->count(),
            'revenue_total' => (float) $success->sum('amount'),
            'months' => $months,
            'monthly_revenue' => $months->map(fn ($m) => (float) ($revenueByMonth->get($m, collect())->sum('amount')))->values(),
            'monthly_bookings' => $months->map(fn ($m) => $bookingsByMonth->get($m, collect())->count())->values(),
        ], 'إحصاءات فنادقك.');
    }
}