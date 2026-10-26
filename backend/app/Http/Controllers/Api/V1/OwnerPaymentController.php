<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerPaymentController extends Controller
{
    use ApiResponse;

    public function __construct(protected PaymentService $paymentService) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending,success,failed,under_review,refunded,cancelled'],
        ]);

        $hotelIds = $request->user()->ownedHotels()->pluck('id');

        $payments = Payment::with(['paymentMethod', 'booking', 'user'])
            ->whereHas('booking', fn($q) => $q->whereIn('hotel_id', $hotelIds))
            ->when(!empty($validated['status']), fn($q) => $q->where('payment_status', $validated['status']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => __('messages.owner_payments_list'),
            'data' => PaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function review(ReviewPaymentRequest $request, Payment $payment): JsonResponse
    {
        $this->authorize('review', $payment);

        if ($payment->booking->booking_status === 'cancelled') {
            return $this->errorResponse(__('messages.payment_review_cancelled'), 422);
        }

        $payment = $this->paymentService->reviewPaymentByOwner(
            $request->user(),
            $payment,
            $request->validated('action'),
            $request->validated('admin_note')
        );

        $payment->load(['paymentMethod', 'booking']);

        return $this->successResponse(
            new PaymentResource($payment),
            $request->validated('action') === 'approve'
                ? __('messages.payment_approved_ok')
                : __('messages.payment_rejected_ok')
        );
    }
}