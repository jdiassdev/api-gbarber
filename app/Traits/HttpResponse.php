<?php

namespace App\Traits;

use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait HttpResponse
{
    public function success(string $message, int $status, array|Model|JsonResource $data = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $status,
            'data' => $data
        ], $status, [], JSON_UNESCAPED_SLASHES);
    }

    public function error(string $message, int $status, array|MessageBag $errors = [], array $data = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $status,
            'errors' => $errors,
            'data' => $data
        ], $status, [], JSON_UNESCAPED_SLASHES);
    }
}
