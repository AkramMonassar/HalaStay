<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminCountryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $countries = Country::orderBy('id')->get();

        return response()->json([
            'success' => true,
            'message' => 'كل الدول.',
            'data' => CountryResource::collection($countries),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'name_en' => ['nullable', 'string', 'max:191'],
        ]);

        $country = Country::create([
            'name' => $validated['name'],
            'name_en' => $validated['name_en'] ?? null,
        ]);

        Cache::forget('countries');

        return $this->successResponse(new CountryResource($country), 'تم إضافة الدولة.', 201);
    }

    public function update(Request $request, Country $country): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:191'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:191'],
        ]);

        $country->update([
            'name' => $validated['name'] ?? $country->name,
            'name_en' => array_key_exists('name_en', $validated) ? $validated['name_en'] : $country->name_en,
        ]);

        Cache::forget('countries');

        return $this->successResponse(new CountryResource($country), 'تم تحديث الدولة.');
    }
}