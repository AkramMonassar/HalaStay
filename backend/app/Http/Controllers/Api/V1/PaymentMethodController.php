<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PaymentMethodController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $methods = Cache::remember('payment_methods', 3600, fn () => PaymentMethod::where('is_active', true)->get());

        return $this->successResponse(PaymentMethodResource::collection($methods), 'طرق الدفع المتاحة.');
    }
}