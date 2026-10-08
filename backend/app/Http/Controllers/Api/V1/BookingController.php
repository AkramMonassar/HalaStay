<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Payment;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(protected BookingService $bookingService) {}

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->createBooking(
            $request->user(),
            $request->validated()
        );

        $booking->load(['hotel', 'accommodationType']);

        return $this->successResponse(
            new BookingResource($booking),
            'تم إنشاء الحجز بنجاح، يرجى إتمام الدفع.',
            201
        );
    }

    public function userBookings(Request $request): JsonResponse
    {
        $bookings = Booking::with(['hotel', 'accommodationType', 'payments'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'قائمة حجوزاتك.',
            'data' => BookingResource::collection($bookings),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        $booking->load(['hotel', 'accommodationType', 'payments.paymentMethod', 'statusHistory.changedBy']);

        return $this->successResponse(new BookingResource($booking), 'تفاصيل الحجز.');
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = $this->bookingService->cancelBooking(
            $request->user(),
            $booking,
            $validated['reason'] ?? null
        );

        $booking->load(['hotel', 'accommodationType']);
        // إبطال أي دفعة مفتوحة مرتبطة بالحجز الملغى
        $voided = Payment::where('booking_id', $booking->id)
            ->whereIn('payment_status', ['pending', 'under_review'])
            ->update(['payment_status' => 'cancelled']);

        // إشعار المالك بالإلغاء — والـ Observer يبثه لحظياً
        $owner = $booking->accommodationType?->hotel?->owner;
        if ($owner) {
            Notification::create([
                'user_id' => $owner->id,
                'type' => 'booking_cancelled',
                'title' => 'تم إلغاء الحجز ' . $booking->booking_number,
                'body' => $voided
                    ? 'ألغى الضيف الحجز بعد رفع دفعة — تم إبطال الدفعة المفتوحة المرتبطة.'
                    : 'ألغى الضيف الحجز قبل اكتماله.',
            ]);
        }

        return $this->successResponse(new BookingResource($booking), 'تم إلغاء الحجز بنجاح.');
    }
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['accommodationType.hotel', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return $this->successResponse(BookingResource::collection($bookings), 'حجوزاتك.');
    }
}
