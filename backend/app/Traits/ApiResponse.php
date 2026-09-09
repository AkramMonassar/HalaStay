<?php

namespace App\Traits;

trait ApiResponse
{
    public function successResponse($data = null, string $message = 'تمت العملية بنجاح', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function errorResponse(string $message = 'حدث خطأ', int $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $code);
    }
}