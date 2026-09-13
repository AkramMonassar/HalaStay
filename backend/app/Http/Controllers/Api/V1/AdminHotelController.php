<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use App\Services\HotelService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminHotelController extends Controller
{
    use ApiResponse;

    public function __construct(protected HotelService $hotelService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:pending,approved,rejected,suspended'],
        ]);

        $hotels = Hotel::with(['city', 'owner', 'images'])
            ->when(!empty($validated['status']), fn ($q) => $q->where('status', $validated['status']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'قائمة الفنادق.',
            'data' => HotelResource::collection($hotels),
            'meta' => [
                'current_page' => $hotels->currentPage(),
                'last_page' => $hotels->lastPage(),
                'per_page' => $hotels->perPage(),
                'total' => $hotels->total(),
            ],
        ]);
    }

    public function approve(Request $request, Hotel $hotel): JsonResponse
    {
        $hotel = $this->hotelService->approveHotel($hotel);

        return $this->successResponse(
            new HotelResource($hotel->load(['city', 'owner'])),
            'تم اعتماد الفندق، وأصبح ظاهراً في نتائج البحث.'
        );
    }

    public function reject(Request $request, Hotel $hotel): JsonResponse
    {
        $hotel = $this->hotelService->rejectHotel($hotel);

        return $this->successResponse(
            new HotelResource($hotel->load(['city', 'owner'])),
            'تم رفض الفندق.'
        );
    }
}