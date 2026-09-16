<?php

namespace Tests\Feature;

use App\Models\AccommodationType;
use App\Models\City;
use App\Models\Hotel;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $tourist;
    protected Hotel $hotel;
    protected AccommodationType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->tourist = User::factory()->createone(['role' => 'tourist']);
        $owner = User::factory()->createone(['role' => 'hotel_owner']);

        $this->hotel = Hotel::create([
            'owner_id' => $owner->id,
            'city_id' => City::first()->id,
            'name' => 'فندق الاختبار',
            'slug' => Str::random(10),
            'star_rating' => 4,
            'status' => 'approved',
            'is_active' => true,
        ]);

        $this->type = AccommodationType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'غرفة اختبار',
            'stay_type' => 'room',
            'max_adults' => 2,
            'max_children' => 1,
            'total_units' => 2,
            'base_price' => 300,
            'currency_code' => 'SAR',
            'is_active' => true,
        ]);
    }

    protected function book(array $overrides = [])
    {
        return $this->actingAs($this->tourist, 'sanctum')->postJson('/api/v1/bookings', array_merge([
            'accommodation_type_id' => $this->type->id,
            'check_in' => '2026-11-01',
            'check_out' => '2026-11-03',
            'adults' => 2,
            'children' => 0,
            'rooms' => 1,
        ], $overrides));
    }

    public function test_tourist_can_book_available_type(): void
    {
        $this->book()
            ->assertStatus(201)
            ->assertJsonPath('data.booking_status', 'pending_payment');

        $this->assertDatabaseHas('bookings', [
            'accommodation_type_id' => $this->type->id,
            'rooms_count' => 1,
            'total_price' => 600,
        ]);
    }

    public function test_capacity_rule_rejects_overcrowded_booking(): void
    {
        $this->book(['adults' => 10])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['adults']]);
    }

    public function test_units_are_blocked_once_fully_booked(): void
    {
        $this->book(['rooms' => 2])->assertStatus(201);

        $this->book(['rooms' => 1])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['rooms']]);
    }

    public function test_cancellation_frees_units(): void
    {
        $booking = $this->book(['rooms' => 2])->json('data');

        $this->actingAs($this->tourist, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking['id']}/cancel", ['reason' => 'اختبار'])
            ->assertStatus(200);

        $this->book(['rooms' => 2])->assertStatus(201);
    }

    public function test_search_hides_unapproved_hotels(): void
    {
        $this->hotel->update(['status' => 'pending']);

        $this->getJson('/api/v1/search?city_id=' . $this->hotel->city_id . '&check_in=2026-11-01&check_out=2026-11-03&adults=2&rooms=1')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}