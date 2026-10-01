<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HotelReviewService
{
    public function create(User $user, Booking $booking, array $data): Review
    {
        return DB::transaction(function () use ($user, $booking, $data) {
            $review = Review::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'hotel_id' => $booking->accommodationType->hotel_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_approved' => true,
            ]);

            $this->recalculateScore($review->hotel_id);

            $owner = $booking->accommodationType->hotel->owner;
            if ($owner) {
                Notification::create([
                    'user_id' => $owner->id,
                    'type' => 'review',
                    'title' => 'مراجعة جديدة لفندقك',
                    'body' => "قيّم {$user->name} إقامته بـ {$review->rating} نجوم.",
                ]);
            }

            return $review;
        });
    }

    public function recalculateScore(int $hotelId): void
    {
        $avg = Review::where('hotel_id', $hotelId)->where('is_approved', true)->avg('rating');
        Hotel::where('id', $hotelId)->update(['review_score' => $avg ? round($avg, 1) : null]);
    }
}