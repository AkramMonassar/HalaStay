<?php

namespace Tests\Feature;

use App\Models\AccommodationType;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function makeCompletedBooking(): Booking
    {
        $owner = User::create(['name' => 'Owner', 'email' => 'owner@r.com', 'password' => Hash::make('password123'), 'role' => 'hotel_owner']);
        $guest = User::create(['name' => 'Guest', 'email' => 'guest@r.com', 'password' => Hash::make('password123'), 'role' => 'tourist']);
        $country = Country::create(['name' => 'Saudi Arabia', 'code' => 'SA']);
        $city = City::create(['country_id' => $country->id, 'name' => 'Riyadh', 'is_active' => true]);
        $hotel = Hotel::create(['owner_id' => $owner->id, 'city_id' => $city->id, 'name' => 'Test Hotel', 'status' => 'approved', 'star_rating' => 3]);
        $type = AccommodationType::create(['hotel_id' => $hotel->id, 'stay_type' => 'room', 'name' => 'Room', 'base_price' => 100, 'max_adults' => 2, 'max_children' => 1, 'units_count' => 1]);

        return Booking::create([
            'user_id' => $guest->id,
            'hotel_id' => $hotel->id,
            'accommodation_type_id' => $type->id,
            'booking_number' => 'HS-REVIEW1',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDay()->toDateString(),
            'adults' => 2,
            'children' => 0,
            'rooms' => 1,
            'booking_status' => 'completed',
            'total_price' => 100,
            'currency_code' => 'SAR',
        ]);
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    public function test_guest_can_review_completed_booking(): void
    {
        $booking = $this->makeCompletedBooking();

        $res = $this->withHeader('Authorization', 'Bearer ' . $this->tokenFor($booking->user))
            ->postJson("/api/v1/bookings/{$booking->id}/review", ['rating' => 5, 'comment' => 'رائع']);

        $res->assertCreated();
        $this->assertDatabaseHas('reviews', ['booking_id' => $booking->id, 'rating' => 5]);
        $this->assertEquals(5.0, $booking->accommodationType->hotel->fresh()->review_score);
    }

    public function test_guest_cannot_review_non_completed_booking(): void
    {
        $booking = $this->makeCompletedBooking();
        $booking->update(['booking_status' => 'confirmed']);

        $this->withHeader('Authorization', 'Bearer ' . $this->tokenFor($booking->user))
            ->postJson("/api/v1/bookings/{$booking->id}/review", ['rating' => 4])
            ->assertForbidden();
    }

    public function test_duplicate_review_is_blocked(): void
    {
        $booking = $this->makeCompletedBooking();
        $headers = ['Authorization' => 'Bearer ' . $this->tokenFor($booking->user)];

        $this->withHeaders($headers)->postJson("/api/v1/bookings/{$booking->id}/review", ['rating' => 4])->assertCreated();
        $this->withHeaders($headers)->postJson("/api/v1/bookings/{$booking->id}/review", ['rating' => 5])->assertForbidden();
    }
}