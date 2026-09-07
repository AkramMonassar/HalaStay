<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'payment_id',
        'gateway_name',
        'request_payload',
        'response_payload',
        'status_code',
        'log_type',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}