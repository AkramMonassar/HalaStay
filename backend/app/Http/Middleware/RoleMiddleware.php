<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً للوصول إلى هذا المورد.',
            ], 401);
        }

        if ($user->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول إلى هذا المورد.',
            ], 403);
        }

        return $next($request);
    }
}