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

class AdminApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function makePendingHotel(User $owner): Hotel
    {
        $hotel = Hotel::create([
            'owner_id' => $owner->id,
            'city_id' => City::first()->id,
            'name' => 'فندق الاعتماد',
            'slug' => Str::random(10),
            'star_rating' => 4,
            'status' => 'pending',
            'is_active' => true,
        ]);

        AccommodationType::create([
            'hotel_id' => $hotel->id,
            'name' => 'غرفة اعتماد',
            'stay_type' => 'room',
            'max_adults' => 2,
            'max_children' => 0,
            'total_units' => 2,
            'base_price' => 250,
            'currency_code' => 'SAR',
            'is_active' => true,
        ]);

        return $hotel;
    }

    public function test_pending_hotel_hidden_then_visible_after_approval(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::factory()->createOne(['role' => 'hotel_owner']);
        $admin = User::factory()->createOne(['role' => 'admin']);
        $hotel = $this->makePendingHotel($owner);

        $query = '/api/v1/search?city_id=' . $hotel->city_id . '&check_in=2026-12-01&check_out=2026-12-02&adults=2&rooms=1';

        $this->getJson($query)->assertStatus(200)->assertJsonCount(0, 'data');

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/hotels/{$hotel->id}/approve")
            ->assertStatus(200);

        $this->getJson($query)->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_owner_cannot_approve_hotels(): void
    {
        $this->seed(DatabaseSeeder::class);

        $owner = User::factory()->createOne(['role' => 'hotel_owner']);
        $hotel = $this->makePendingHotel($owner);

        $this->actingAs($owner, 'sanctum')
            ->patchJson("/api/v1/admin/hotels/{$hotel->id}/approve")
            ->assertStatus(403);
    }
}