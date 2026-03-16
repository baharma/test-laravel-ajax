<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function apiSuccess(mixed $data = [], string $message = null): JsonResponse
    {
        return $this->buildResponse($data, $message);
    }

    private function buildResponse(mixed $data = [], string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => $code,
            'data' => $data ?? [],
            'message' => $message ?? 'OK',
        ], $code);
    }

    public function apiError(array|null $data = [], string $message = null): JsonResponse
    {
        return $this->buildResponse($data, $message, 400);
    }
}
