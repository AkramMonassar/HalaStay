<?php

namespace Tests\Feature;

use App\Models\AccommodationType;
use App\Models\City;
use App\Models\Hotel;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $tourist;
    protected User $owner;
    protected array $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->tourist = User::factory()->createone(['role' => 'tourist']);
        $this->owner = User::factory()->createone(['role' => 'hotel_owner']);

        $hotel = Hotel::create([
            'owner_id' => $this->owner->id,
            'city_id' => City::first()->id,
            'name' => 'فندق الدفع',
            'slug' => Str::random(10),
            'star_rating' => 3,
            'status' => 'approved',
            'is_active' => true,
        ]);

        $type = AccommodationType::create([
            'hotel_id' => $hotel->id,
            'name' => 'غرفة دفع',
            'stay_type' => 'room',
            'max_adults' => 2,
            'max_children' => 0,
            'total_units' => 3,
            'base_price' => 200,
            'currency_code' => 'SAR',
            'is_active' => true,
        ]);

        $this->booking = $this->actingAs($this->tourist, 'sanctum')->postJson('/api/v1/bookings', [
            'accommodation_type_id' => $type->id,
            'check_in' => '2026-12-01',
            'check_out' => '2026-12-02',
            'adults' => 2,
            'rooms' => 1,
        ])->json('data');
    }

    protected function createManualPayment(string $methodKey): array
    {
        $method = PaymentMethod::where('method_key', $methodKey)->first();

        return $this->actingAs($this->tourist, 'sanctum')->postJson('/api/v1/payments/manual', [
            'booking_id' => $this->booking['id'],
            'payment_method_id' => $method->id,
            'transaction_id' => 'TRX-TEST',
        ])->json('data');
    }

    public function test_manual_payment_starts_under_review_and_moves_booking(): void
    {
        $payment = $this->createManualPayment('bank_transfer');

        $this->assertSame('under_review', $payment['payment_status']);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking['id'],
            'booking_status' => 'pending_confirmation',
        ]);
    }

    public function test_owner_approval_confirms_booking_and_payment(): void
    {
        $payment = $this->createManualPayment('bank_transfer');

        $this->actingAs($this->owner, 'sanctum')
            ->patchJson("/api/v1/owner/payments/{$payment['id']}/review", ['action' => 'approve'])
            ->assertStatus(200);

        $this->assertDatabaseHas('payments', ['id' => $payment['id'], 'payment_status' => 'success']);
        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking['id'],
            'booking_status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    public function test_owner_rejection_returns_booking_to_pending_payment(): void
    {
        $payment = $this->createManualPayment('e_wallet');

        $this->actingAs($this->owner, 'sanctum')
            ->patchJson("/api/v1/owner/payments/{$payment['id']}/review", ['action' => 'reject', 'admin_note' => 'غير واضح'])
            ->assertStatus(200);

        $this->assertDatabaseHas('payments', ['id' => $payment['id'], 'payment_status' => 'failed']);
        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking['id'],
            'booking_status' => 'pending_payment',
        ]);
    }

    public function test_tourist_cannot_review_payments(): void
    {
        $payment = $this->createManualPayment('bank_transfer');

        $this->actingAs($this->tourist, 'sanctum')
            ->patchJson("/api/v1/owner/payments/{$payment['id']}/review", ['action' => 'approve'])
            ->assertStatus(403);
    }
}