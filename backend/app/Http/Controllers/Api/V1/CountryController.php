<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Traits\ApiResponse;

class CountryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $countries = Country::where('is_active', true)->get();
        return $this->successResponse(
            CountryResource::collection($countries),
            'تم جلب الدول بنجاح.'
        );
    }
}