<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchHotelsRequest;
use App\Http\Resources\HotelSearchResource;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function index(SearchHotelsRequest $request, SearchService $searchService): JsonResponse
    {
        $filters = $request->validated();

        $hotels = $searchService->search($filters);

        $rooms = (int) $filters['rooms'];
        foreach ($hotels as $hotel) {
            $hotel->setAttribute(
                'available_types',
                $searchService->availableTypesFor($hotel, $filters['check_in'], $filters['check_out'], $rooms)
            );
        }

        return response()->json([
            'success' => true,
            'message' => $hotels->total() > 0
                ? 'تم العثور على النتائج.'
                : 'لا توجد فنادق مطابقة لمعايير البحث.',
            'data' => HotelSearchResource::collection($hotels),
            'meta' => [
                'current_page' => $hotels->currentPage(),
                'last_page' => $hotels->lastPage(),
                'per_page' => $hotels->perPage(),
                'total' => $hotels->total(),
            ],
        ]);
    }
}