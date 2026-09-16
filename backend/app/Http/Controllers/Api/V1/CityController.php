<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CityController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $cities = Cache::remember('cities', 3600, fn () => City::with('country')->where('is_active', true)->get());

        return $this->successResponse(CityResource::collection($cities), 'قائمة المدن.');
    }
}