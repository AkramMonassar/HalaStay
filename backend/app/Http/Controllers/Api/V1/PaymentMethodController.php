<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Traits\ApiResponse;

class PaymentMethodController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $methods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        return $this->successResponse(
            PaymentMethodResource::collection($methods),
            'تم جلب طرق الدفع بنجاح.'
        );
    }
}