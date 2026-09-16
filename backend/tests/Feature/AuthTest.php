<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_as_tourist(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'سائح جديد',
            'email' => 'tourist@test.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'tourist',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'email', 'role']]]);

        $this->assertDatabaseHas('users', ['email' => 'tourist@test.com', 'role' => 'tourist']);
    }

    public function test_registration_rejects_weak_password(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'سائح',
            'email' => 'x@test.com',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'tourist',
        ])->assertStatus(422);
    }

    public function test_user_can_login_and_receive_token(): void
    {
        User::factory()->createone([
            'email' => 'akram@test.com',
            'password' => bcrypt('Password123'),
            'role' => 'tourist',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'akram@test.com',
            'password' => 'Password123',
        ])->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->createone([
            'email' => 'akram@test.com',
            'password' => bcrypt('Password123'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'akram@test.com',
            'password' => 'wrong',
        ])->assertStatus(422);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->createone([
            'email' => 'stopped@test.com',
            'password' => bcrypt('Password123'),
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'stopped@test.com',
            'password' => 'Password123',
        ])->assertStatus(403);
    }

    public function test_me_requires_token(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->createone(['role' => 'tourist']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('data.user.email', $user->email);
    }
}