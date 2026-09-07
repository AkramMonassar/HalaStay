<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'owner_id',
        'city_id',
        'name',
        'slug',
        'description',
        'address',
        'phone',
        'email',
        'latitude',
        'longitude',
        'star_rating',
        'review_score',
        'status',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'review_score' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function images()
    {
        return $this->hasMany(HotelImage::class);
    }

    public function accommodationTypes()
    {
        return $this->hasMany(AccommodationType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}