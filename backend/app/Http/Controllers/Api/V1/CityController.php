<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Traits\ApiResponse;

class CityController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $cities = City::where('is_active', true)->with('country')->get();
        return $this->successResponse(
            CityResource::collection($cities),
            'تم جلب المدن بنجاح.'
        );
    }
}