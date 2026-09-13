<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    public function view(User $user, Booking $booking)
    {
        return ($booking->user_id === $user->id || $user->role === 'admin')
            ? Response::allow()
            : Response::deny('لا يمكنك الاطلاع على هذا الحجز.');
    }

    public function cancel(User $user, Booking $booking)
    {
        return $booking->user_id === $user->id
            ? Response::allow()
            : Response::deny('لا يمكنك إلغاء حجز لا تملكه.');
    }

    public function process(User $user, Booking $booking)
    {
        return $booking->hotel?->owner_id === $user->id
            ? Response::allow()
            : Response::deny('لا يمكنك إدارة حجوزات فندق لا تملكه.');
    }
}