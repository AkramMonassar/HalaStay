<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStatusHistory extends Model
{
    protected $table = 'booking_status_history';

    const UPDATED_AT = null;

    protected $fillable = [
        'booking_id',
        'changed_by',
        'old_status',
        'new_status',
        'note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}