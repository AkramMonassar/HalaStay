<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(protected BookingService $bookingService)
    {
    }

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
}