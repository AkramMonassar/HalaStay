<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * المصنع المركزي: يخزّن المفتاح + المعاملات، ويحفظ fallback عربي في title/body.
     * الترجمة الفعلية تحدث عند العرض في NotificationResource بلسان القارئ.
     */
    public static function send(User $user, string $type, array $params = []): Notification
    {
        // fallback عربي مضمون للتخزين (حتى لو فشل المعجم عند العرض)
        $title = __('notif.' . $type . '.title', $params, 'ar');
        $body  = __('notif.' . $type . '.body', $params, 'ar');

        return Notification::create([
            'user_id'  => $user->id,
            'type'     => $type,
            'title'    => $title,
            'body'     => $body,
            'params'   => $params ?: null,
            'is_read'  => false,
        ]);
    }
}