<?php

namespace Tests\Feature;

use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationBroadcastTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Guest',
            'email' => 'g@x.com',
            'password' => Hash::make('password123'),
            'role' => 'tourist',
        ]);
    }

    public function test_creating_notification_broadcasts_on_owner_private_channel(): void
    {
        Event::fake([NotificationCreated::class]);

        $user = $this->makeUser();

        Notification::create([
            'user_id' => $user->id,
            'type' => 'booking_confirmed',
            'title' => 'تم تأكيد حجزك',
            'body' => 'نراك قريباً.',
        ]);

        Event::assertDispatched(NotificationCreated::class, function (NotificationCreated $event) use ($user) {
            $channel = $event->broadcastOn()[0];

            return $channel instanceof PrivateChannel
                && $channel->name === 'private-user.' . $user->id;
        });
    }

    public function test_broadcast_payload_carries_id_type_and_title(): void
    {
        Event::fake([NotificationCreated::class]);

        $user = $this->makeUser();

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'payment_reviewed',
            'title' => 'تمت مراجعة دفعتك',
            'body' => 'ناجحة.',
        ]);

        Event::assertDispatched(NotificationCreated::class, function (NotificationCreated $event) use ($notification) {
            $payload = $event->broadcastWith();

            return $payload['id'] === $notification->id
                && $payload['type'] === 'payment_reviewed'
                && $payload['title'] === 'تمت مراجعة دفعتك';
        });
    }
}