<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /** إرسال إشعار داخلي لمستخدم (نسخة v1 بسيطة وثابتة) */
    public static function send(User $user, string $title, string $body, ?string $type = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'is_read' => false,
        ]);
    }
}