<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccommodationTypeResource;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use App\Services\AvailabilityService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    use ApiResponse;

    public function __construct(protected AvailabilityService $availabilityService) {}

    public function show(Hotel $hotel): JsonResponse
    {
        if ($hotel->status !== 'approved' || !$hotel->is_active) {
            return $this->errorResponse(__('messages.hotel_unavailable'), 404);
        }

        $hotel->load([
            'city',
            'images' => fn($q) => $q->orderBy('sort_order'),
            'accommodationTypes' => fn($q) => $q->where('is_active', true),
        ]);

        return $this->successResponse(new HotelResource($hotel), __('messages.hotel_details'));
    }

    public function rooms(Request $request, Hotel $hotel): JsonResponse
    {
        if ($hotel->status !== 'approved' || !$hotel->is_active) {
            return $this->errorResponse(__('messages.hotel_unavailable'), 404);
        }

        $validated = $request->validate([
            'check_in' => ['nullable', 'date'],
            'check_out' => ['nullable', 'date', 'after:check_in'],
            'rooms' => ['nullable', 'integer', 'min:1'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
        ], [
            'check_in.date' => __('validation.date_format'),
            'check_out.date' => __('validation.date_format'),
            'check_out.after' => __('validation.check_out_after'),
            'rooms.min' => __('validation.rooms_min'),
        ]);

        if (!empty($validated['check_in']) && !empty($validated['check_out'])) {
            $available = $this->availabilityService->typesForHotel(
                $hotel,
                $validated['check_in'],
                $validated['check_out'],
                (int) ($validated['rooms'] ?? 1)
            );

            return $this->successResponse(
                AccommodationTypeResource::collection($available),
                __('messages.types_available_dates')
            );
        }

        $types = $hotel->accommodationTypes()->where('is_active', true)->get();

        return $this->successResponse(
            AccommodationTypeResource::collection($types),
            __('messages.types_for_hotel')
        );
    }

    public function availability(Request $request, Hotel $hotel): JsonResponse
    {
        if ($hotel->status !== 'approved' || !$hotel->is_active) {
            return $this->errorResponse(__('messages.hotel_unavailable'), 404);
        }

        $validated = $request->validate([
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms' => ['nullable', 'integer', 'min:1'],
            'accommodation_type_id' => ['nullable', 'exists:accommodation_types,id'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
        ], [
            'check_in.required' => __('validation.check_in_required'),
            'check_out.required' => __('validation.check_out_required'),
            'check_out.after' => __('validation.check_out_after'),
            'rooms.min' => __('validation.rooms_min'),
            'accommodation_type_id.exists' => __('messages.type_not_in_hotel'),
        ]);

        $rooms = (int) ($validated['rooms'] ?? 1);

        if (!empty($validated['accommodation_type_id'])) {
            $type = $hotel->accommodationTypes()
                ->where('id', $validated['accommodation_type_id'])
                ->where('is_active', true)
                ->first();

            if (!$type) {
                return $this->errorResponse(__('messages.type_not_in_hotel'), 404);
            }

            $availableUnits = $this->availabilityService->availableUnits(
                $type,
                $validated['check_in'],
                $validated['check_out']
            );

            return $this->successResponse([
                'accommodation_type_id' => $type->id,
                'name' => $type->name,
                'total_units' => $type->total_units,
                'available_units' => $availableUnits,
                'requested_rooms' => $rooms,
                'is_available' => $availableUnits >= $rooms,
            ], __('messages.availability_status'));
        }

        $available = $this->availabilityService->typesForHotel(
            $hotel,
            $validated['check_in'],
            $validated['check_out'],
            $rooms,
            (int) ($validated['adults'] ?? 0) + (int) ($validated['children'] ?? 0)
        );

        return $this->successResponse(
            AccommodationTypeResource::collection($available),
            __('messages.types_available')
        );
    }
}