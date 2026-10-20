<?php

namespace Tests\Feature;

use App\Models\AccommodationType;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExpireUnpaidBookingsTest extends TestCase
{
    use RefreshDatabase;

    private function makeBooking(string $status, string $createdAt): Booking
    {
        $owner = User::create(['name' => 'Owner', 'email' => 'o@x.com', 'password' => Hash::make('password123'), 'role' => 'hotel_owner']);
        $guest = User::create(['name' => 'Guest', 'email' => 'g@x.com', 'password' => Hash::make('password123'), 'role' => 'tourist']);
        $country = Country::create(['name' => 'Saudi Arabia', 'code' => 'SA']);
        $city = City::create(['country_id' => $country->id, 'name' => 'Riyadh', 'is_active' => true]);
        $hotel = Hotel::create(['owner_id' => $owner->id, 'city_id' => $city->id, 'name' => 'H', 'status' => 'approved', 'star_rating' => 3]);
        $type = AccommodationType::create(['hotel_id' => $hotel->id, 'stay_type' => 'room', 'name' => 'R', 'base_price' => 100, 'max_adults' => 2, 'max_children' => 1, 'total_units' => 1, 'currency_code' => 'SAR', 'is_active' => true]);

        $booking = Booking::create([
            'user_id' => $guest->id,
            'hotel_id' => $hotel->id,
            'accommodation_type_id' => $type->id,
            'booking_number' => 'HS-EXP-' . uniqid(),
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(6)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'rooms' => 1,
            'booking_status' => $status,
            'total_price' => 100,
            'currency_code' => 'SAR',
        ]);

        // سفر زمني: نحرّك ميلاد الحجز بدل انتظار الواقع
        $booking->forceFill(['created_at' => $createdAt])->save();

        return $booking;
    }

    public function test_unpaid_booking_older_than_window_expires(): void
    {
        $booking = $this->makeBooking('pending_payment', now()->subHours(30)->toDateTimeString());

        Artisan::call('bookings:expire-unpaid');

        $this->assertEquals('expired', $booking->fresh()->booking_status);
        $this->assertDatabaseHas('notifications', ['user_id' => $booking->user_id, 'type' => 'booking_expired']);
    }

    public function test_recent_unpaid_booking_stays(): void
    {
        $booking = $this->makeBooking('pending_payment', now()->subHours(2)->toDateTimeString());

        Artisan::call('bookings:expire-unpaid');

        $this->assertEquals('pending_payment', $booking->fresh()->booking_status);
    }

    public function test_paid_booking_never_expires(): void
    {
        $booking = $this->makeBooking('confirmed', now()->subHours(300)->toDateTimeString());

        Artisan::call('bookings:expire-unpaid');

        $this->assertEquals('confirmed', $booking->fresh()->booking_status);
    }
}