<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['nullable', 'string', 'in:tourist,hotel_owner,admin'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $users = User::query()
            ->when(!empty($validated['role']), fn ($q) => $q->where('role', $validated['role']))
            ->when(!empty($validated['search']), function ($q) use ($validated) {
                $term = '%' . $validated['search'] . '%';
                $q->where(fn ($qq) => $qq->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'قائمة المستخدمين.',
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function toggleActive(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return $this->errorResponse('لا يمكنك إيقاف حسابك الحالي.', 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        return $this->successResponse(
            new UserResource($user),
            $user->is_active ? 'تم تفعيل المستخدم.' : 'تم إيقاف المستخدم.'
        );
    }
}