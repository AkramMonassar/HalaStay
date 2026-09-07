<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_number',
        'booking_id',
        'user_id',
        'payment_method_id',
        'amount',
        'currency_code',
        'payment_gateway',
        'transaction_id',
        'gateway_reference',
        'payment_status',
        'receipt_image',
        'admin_note',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function gatewayLogs()
    {
        return $this->hasMany(PaymentGatewayLog::class);
    }
}