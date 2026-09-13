<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    use ApiResponse;

    public function stats(): JsonResponse
    {
        $usersByRole = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');
        $hotelsByStatus = Hotel::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $bookingsByStatus = Booking::selectRaw('booking_status, COUNT(*) as total')->groupBy('booking_status')->pluck('total', 'booking_status');

        return $this->successResponse([
            'users' => [
                'total' => $usersByRole->sum(),
                'tourists' => $usersByRole['tourist'] ?? 0,
                'owners' => $usersByRole['hotel_owner'] ?? 0,
                'admins' => $usersByRole['admin'] ?? 0,
            ],
            'hotels' => [
                'total' => $hotelsByStatus->sum(),
                'pending' => $hotelsByStatus['pending'] ?? 0,
                'approved' => $hotelsByStatus['approved'] ?? 0,
            ],
            'bookings' => [
                'total' => $bookingsByStatus->sum(),
                'by_status' => $bookingsByStatus,
            ],
            'payments' => [
                'under_review' => Payment::where('payment_status', 'under_review')->count(),
                'total_success_amount' => (float) Payment::where('payment_status', 'success')->sum('amount'),
            ],
        ], 'إحصاءات لوحة الأدمن.');
    }

    public function bookings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending_payment,pending_confirmation,confirmed,cancelled,completed,expired'],
        ]);

        $bookings = Booking::with(['hotel', 'user', 'accommodationType'])
            ->when(!empty($validated['status']), fn ($q) => $q->where('booking_status', $validated['status']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'كل الحجوزات.',
            'data' => BookingResource::collection($bookings),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending,success,failed,under_review,refunded'],
        ]);

        $payments = Payment::with(['paymentMethod', 'booking.hotel', 'user'])
            ->when(!empty($validated['status']), fn ($q) => $q->where('payment_status', $validated['status']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'كل الدفعات.',
            'data' => PaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }
}