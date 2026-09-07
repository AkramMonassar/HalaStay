<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationType extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'stay_type',
        'description',
        'max_adults',
        'max_children',
        'total_units',
        'base_price',
        'currency_code',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}