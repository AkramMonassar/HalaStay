<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // إشعار حديث يحمل type + params → تُترجم بلسان القارئ
        if ($this->type && $this->params) {
            $locale = app()->getLocale();
            return [
                'id'         => $this->id,
                'type'       => $this->type,
                'title'      => __('notif.' . $this->type . '.title', $this->params, $locale),
                'body'       => __('notif.' . $this->type . '.body', $this->params, $locale),
                'is_read'    => $this->is_read,
                'created_at' => $this->created_at,
            ];
        }

        // إشعار قديم (يتيمة) بلا params → اعرض المخزَّن العربي بكرامة
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'title'      => $this->title,
            'body'       => $this->body,
            'is_read'    => $this->is_read,
            'created_at' => $this->created_at,
        ];
    }
}