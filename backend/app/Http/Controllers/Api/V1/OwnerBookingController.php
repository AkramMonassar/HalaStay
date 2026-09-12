<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerBookingController extends Controller
{
    use ApiResponse;

    public function __construct(protected BookingService $bookingService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending_payment,pending_confirmation,confirmed,cancelled,completed,expired'],
        ]);

        $hotelIds = $request->user()->ownedHotels()->pluck('id');

        $bookings = Booking::with(['hotel', 'user', 'accommodationType', 'payments'])
            ->whereIn('hotel_id', $hotelIds)
            ->when(!empty($validated['status']), fn ($q) => $q->where('booking_status', $validated['status']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'حجوزات فنادقك.',
            'data' => BookingResource::collection($bookings),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function confirm(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = $this->bookingService->confirmBookingByOwner(
            $request->user(),
            $booking,
            $validated['note'] ?? null
        );

        $booking->load(['hotel', 'accommodationType']);

        return $this->successResponse(new BookingResource($booking), 'تم تأكيد الحجز بنجاح.');
    }

    public function reject(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = $this->bookingService->rejectBookingByOwner(
            $request->user(),
            $booking,
            $validated['reason'] ?? null
        );

        $booking->load(['hotel', 'accommodationType']);

        return $this->successResponse(new BookingResource($booking), 'تم رفض الحجز وتحرير الوحدات.');
    }
}