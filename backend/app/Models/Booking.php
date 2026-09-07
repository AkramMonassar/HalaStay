<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_number',
        'user_id',
        'hotel_id',
        'accommodation_type_id',
        'check_in',
        'check_out',
        'nights',
        'adults',
        'children',
        'rooms_count',
        'total_price',
        'currency_code',
        'booking_status',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function accommodationType()
    {
        return $this->belongsTo(AccommodationType::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}