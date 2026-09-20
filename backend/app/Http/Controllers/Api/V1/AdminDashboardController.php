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
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('Y-m'));

        $bookings = Booking::all();
        $payments = Payment::all();
        $success = $payments->where('payment_status', 'success');

        $bookingsByMonth = $bookings->groupBy(fn ($b) => $b->created_at->format('Y-m'));
        $revenueByMonth = $success->groupBy(fn ($p) => $p->updated_at->format('Y-m'));

        return $this->successResponse([
            'users' => [
                'total' => User::count(),
                'tourists' => User::where('role', 'tourist')->count(),
                'owners' => User::where('role', 'hotel_owner')->count(),
                'admins' => User::where('role', 'admin')->count(),
            ],
            'hotels' => [
                'total' => Hotel::count(),
                'approved' => Hotel::where('status', 'approved')->count(),
                'pending' => Hotel::where('status', 'pending')->count(),
            ],
            'bookings' => [
                'total' => $bookings->count(),
                'by_status' => $bookings->groupBy('booking_status')->map->count(),
            ],
            'payments' => [
                'under_review' => $payments->where('payment_status', 'under_review')->count(),
                'total_success_amount' => (float) $success->sum('amount'),
            ],
            'months' => $months,
            'monthly_bookings' => $months->map(fn ($m) => $bookingsByMonth->get($m, collect())->count())->values(),
            'monthly_revenue' => $months->map(fn ($m) => (float) ($revenueByMonth->get($m, collect())->sum('amount')))->values(),
        ], 'إحصاءات المنصة.');
    }

    public function bookings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending_payment,pending_confirmation,confirmed,cancelled,completed,expired'],
        ]);

        $bookings = Booking::with(['user', 'accommodationType.hotel'])
            ->when(!empty($validated['status']), fn ($q) => $q->where('booking_status', $validated['status']))
            ->latest()
            ->get();

        return $this->successResponse(BookingResource::collection($bookings), 'كل الحجوزات.');
    }

    public function payments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending,success,failed,under_review,refunded'],
        ]);

        $payments = Payment::with(['paymentMethod', 'booking', 'user'])
            ->when(!empty($validated['status']), fn ($q) => $q->where('payment_status', $validated['status']))
            ->latest()
            ->get();

        return $this->successResponse(PaymentResource::collection($payments), 'كل الدفعات.');
    }
}