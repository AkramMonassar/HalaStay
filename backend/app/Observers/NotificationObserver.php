<?php

namespace App\Observers;

use App\Events\NotificationCreated;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationObserver
{
    public function created(Notification $notification): void
    {
        try {
            event(new NotificationCreated($notification));
        } catch (\Throwable $e) {
            Log::warning('تم تخطي البث اللحظي: ' . $e->getMessage());
        }
    }
}