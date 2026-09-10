<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManualPaymentRequest;
use App\Http\Requests\UploadReceiptRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function store(StoreManualPaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->createManualPayment(
            $request->user(),
            $request->validated(),
            $request->file('receipt')
        );

        $payment->load(['paymentMethod', 'booking']);

        return $this->successResponse(
            new PaymentResource($payment),
            'تم إنشاء الدفعة بنجاح وهي بانتظار المراجعة.',
            201
        );
    }

    public function uploadReceipt(UploadReceiptRequest $request, Payment $payment): JsonResponse
    {
        $payment = $this->paymentService->uploadReceipt(
            $request->user(),
            $payment,
            $request->file('receipt')
        );

        $payment->load(['paymentMethod', 'booking']);

        return $this->successResponse(
            new PaymentResource($payment),
            'تم رفع إشعار الدفع بنجاح.'
        );
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        $payment->load(['paymentMethod', 'booking.hotel']);

        $user = $request->user();
        $isOwner = $payment->user_id === $user->id;
        $isAdmin = $user->role === 'admin';
        $isHotelOwner = $payment->booking?->hotel?->owner_id === $user->id;

        if (!$isOwner && !$isAdmin && !$isHotelOwner) {
            return $this->errorResponse('لا يمكنك الاطلاع على هذه الدفعة.', 403);
        }

        return $this->successResponse(new PaymentResource($payment), 'تفاصيل الدفعة.');
    }
}