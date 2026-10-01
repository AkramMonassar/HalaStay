<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Booking;
use App\Models\Review;
use App\Services\HotelReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function storeForBooking(Request $request, Booking $booking, HotelReviewService $reviews): JsonResponse
    {
        if (! $request->user()->can('createForBooking', $booking)) {
            return $this->errorResponse('لا تملك صلاحية مراجعة هذا الحجز.', 403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = $reviews->create($request->user(), $booking, $validated);

        return $this->successResponse(new ReviewResource($review), 'شكراً لك! تم نشر مراجعتك واحتسابها في تقييم الفندق.', 201);
    }

    public function indexForHotel($hotelId): JsonResponse
    {
        $reviews = Review::with('user')->where('hotel_id', $hotelId)->latest()->get();

        return $this->successResponse(ReviewResource::collection($reviews), 'آراء الضيوف.');
    }
}