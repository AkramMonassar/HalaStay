<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdminCityController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $cities = City::with('country')->orderBy('id')->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'كل المدن (بما فيها غير النشطة).',
            'data' => CityResource::collection($cities),
            'meta' => [
                'current_page' => $cities->currentPage(),
                'last_page' => $cities->lastPage(),
                'per_page' => $cities->perPage(),
                'total' => $cities->total(),
            ],
        ]);
    }

    public function store(StoreCityRequest $request): JsonResponse
    {
        $city = City::create([
            'country_id' => $request->validated('country_id'),
            'name' => $request->validated('name'),
            'is_active' => true,
        ]);
        Cache::forget('cities');
        return $this->successResponse(new CityResource($city->load('country')), 'تم إضافة المدينة.', 201);
    }

    public function update(UpdateCityRequest $request, City $city): JsonResponse
    {
        $data = $request->validated();

        $city->update([
            'name' => $data['name'] ?? $city->name,
            'is_active' => $data['is_active'] ?? $city->is_active,
        ]);
        Cache::forget('cities');
        return $this->successResponse(new CityResource($city->load('country')), 'تم تحديث المدينة.');
    }
}