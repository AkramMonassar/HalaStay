<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    public function view(User $user, Payment $payment)
    {
        $isOwner = $payment->user_id === $user->id;
        $isAdmin = $user->role === 'admin';
        $isHotelOwner = $payment->booking?->hotel?->owner_id === $user->id;

        return ($isOwner || $isAdmin || $isHotelOwner)
            ? Response::allow()
            : Response::deny('لا يمكنك الاطلاع على هذه الدفعة.');
    }

    public function review(User $user, Payment $payment)
    {
        return $payment->booking?->hotel?->owner_id === $user->id
            ? Response::allow()
            : Response::deny('مراجعة الدفعات صلاحية صاحب الفندق فقط.');
    }
}