<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelImagesRequest;
use App\Http\Requests\StoreHotelRequest;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateHotelRequest;
use App\Http\Resources\AccommodationTypeResource;
use App\Http\Resources\HotelImageResource;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use App\Services\HotelService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerHotelController extends Controller
{
    use ApiResponse;

    public function __construct(protected HotelService $hotelService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $hotels = Hotel::with(['city', 'images'])
            ->where('owner_id', $request->user()->id)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'قائمة فنادقك.',
            'data' => HotelResource::collection($hotels),
            'meta' => [
                'current_page' => $hotels->currentPage(),
                'last_page' => $hotels->lastPage(),
                'per_page' => $hotels->perPage(),
                'total' => $hotels->total(),
            ],
        ]);
    }

    public function store(StoreHotelRequest $request): JsonResponse
    {
        $hotel = $this->hotelService->createHotel(
            $request->user(),
            $request->validated(),
            $request->file('images') ?? []
        );

        $hotel->load(['city', 'images']);

        return $this->successResponse(
            new HotelResource($hotel),
            'تم إرسال الفندق بنجاح، وهو بانتظار موافقة الإدارة.',
            201
        );
    }

    public function show(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('update', $hotel);

        $hotel->load(['city', 'images', 'accommodationTypes']);

        return $this->successResponse(new HotelResource($hotel), 'تفاصيل الفندق.');
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('update', $hotel);

        $hotel = $this->hotelService->updateHotel($hotel, $request->validated());

        $hotel->load(['city', 'images']);

        return $this->successResponse(new HotelResource($hotel), 'تم تحديث بيانات الفندق.');
    }

    public function storeImages(StoreHotelImagesRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('update', $hotel);

        $this->hotelService->attachImages($hotel, $request->file('images'));

        $hotel->load('images');

        return $this->successResponse(
            HotelImageResource::collection($hotel->images),
            'تم رفع الصور بنجاح.',
            201
        );
    }

    public function rooms(Request $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('update', $hotel);

        $types = $hotel->accommodationTypes()->latest()->get();

        return $this->successResponse(
            AccommodationTypeResource::collection($types),
            'أنواع الإقامة للفندق.'
        );
    }

    public function storeRoom(StoreRoomRequest $request, Hotel $hotel): JsonResponse
    {
        $this->authorize('update', $hotel);

        $type = $hotel->accommodationTypes()->create([
            'name' => $request->validated('name'),
            'stay_type' => $request->validated('stay_type'),
            'description' => $request->validated('description'),
            'max_adults' => $request->validated('max_adults'),
            'max_children' => $request->validated('max_children') ?? 0,
            'total_units' => $request->validated('total_units'),
            'base_price' => $request->validated('base_price'),
            'currency_code' => $request->validated('currency_code') ?? 'SAR',
            'is_active' => true,
        ]);

        return $this->successResponse(
            new AccommodationTypeResource($type),
            'تم إضافة نوع الإقامة بنجاح.',
            201
        );
    }

}