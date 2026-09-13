<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HotelPolicy
{
    public function update(User $user, Hotel $hotel)
    {
        return $hotel->owner_id === $user->id
            ? Response::allow()
            : Response::deny('لا يمكنك إدارة فندق لا تملكه.');
    }

    public function approve(User $user, Hotel $hotel)
    {
        return $user->role === 'admin'
            ? Response::allow()
            : Response::deny('اعتماد الفنادق صلاحية الأدمن فقط.');
    }
}