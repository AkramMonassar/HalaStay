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
            return $this->errorResponse('الفندق غير موجود أو غير متاح حالياً.', 404);
        }

        $hotel->load([
            'city',
            'images' => fn($q) => $q->orderBy('sort_order'),
            'accommodationTypes' => fn($q) => $q->where('is_active', true),
        ]);

        return $this->successResponse(new HotelResource($hotel), 'تفاصيل الفندق.');
    }

    public function rooms(Request $request, Hotel $hotel): JsonResponse
    {
        if ($hotel->status !== 'approved' || !$hotel->is_active) {
            return $this->errorResponse('الفندق غير موجود أو غير متاح حالياً.', 404);
        }
        $validated = $request->validate([
            'check_in' => ['nullable', 'date'],
            'check_out' => ['nullable', 'date', 'after:check_in'],
            'rooms' => ['nullable', 'integer', 'min:1'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
        ], [
            'check_in.date' => 'صيغة تاريخ الدخول غير صحيحة.',
            'check_out.date' => 'صيغة تاريخ الخروج غير صحيحة.',
            'check_out.after' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.',
            'rooms.min' => 'يجب أن يكون عدد الغرف واحداً على الأقل.',
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
                'أنواع الإقامة المتاحة في التواريخ المحددة.'
            );
        }

        $types = $hotel->accommodationTypes()->where('is_active', true)->get();

        return $this->successResponse(
            AccommodationTypeResource::collection($types),
            'أنواع الإقامة للفندق.'
        );
    }

    public function availability(Request $request, Hotel $hotel): JsonResponse
    {
        if ($hotel->status !== 'approved' || !$hotel->is_active) {
            return $this->errorResponse('الفندق غير موجود أو غير متاح حالياً.', 404);
        }

        $validated = $request->validate([
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms' => ['nullable', 'integer', 'min:1'],
            'accommodation_type_id' => ['nullable', 'exists:accommodation_types,id'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
        ], [
            'check_in.required' => 'تاريخ الدخول مطلوب.',
            'check_out.required' => 'تاريخ الخروج مطلوب.',
            'check_out.after' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.',
            'rooms.min' => 'يجب أن يكون عدد الغرف واحداً على الأقل.',
            'accommodation_type_id.exists' => 'نوع الإقامة غير موجود في هذا الفندق.',
        ]);

        $rooms = (int) ($validated['rooms'] ?? 1);

        if (!empty($validated['accommodation_type_id'])) {
            $type = $hotel->accommodationTypes()
                ->where('id', $validated['accommodation_type_id'])
                ->where('is_active', true)
                ->first();

            if (!$type) {
                return $this->errorResponse('نوع الإقامة غير موجود في هذا الفندق.', 404);
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
            ], 'حالة التوفر لنوع الإقامة المحدد.');
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
            'الأنواع المتاحة في التواريخ المحددة.'
        );
    }
}
