<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'method_key',
        'type',
        'country_id',
        'description',
        'is_active',
        'sort_order',
        'requires_receipt' => 'boolean',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_receipt' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
