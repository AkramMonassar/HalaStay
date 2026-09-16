<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CountryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $countries = Cache::remember('countries', 3600, fn () => Country::with('cities')->get());

        return $this->successResponse(CountryResource::collection($countries), 'قائمة الدول.');
    }
}