<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminPaymentMethodController extends Controller
{
    use ApiResponse;

    public function toggle(PaymentMethod $method): JsonResponse
    {
        $method->update(['is_active' => !$method->is_active]);

        return $this->successResponse(
            new PaymentMethodResource($method),
            $method->is_active ? 'تم تفعيل طريقة الدفع.' : 'تم إيقاف طريقة الدفع.'
        );
    }
}