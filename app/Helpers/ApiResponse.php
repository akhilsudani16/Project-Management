<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\ApiResponseStatus;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        ?string $message = null,
        ?array $meta = null,
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'status' => ApiResponseStatus::SUCCESS->value,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => $meta ? (object) $meta : null,
        ], $statusCode);
    }

    public static function fail(
        ?string $message = null,
        mixed $data = null,
        ?array $meta = null,
        int $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'status' => ApiResponseStatus::FAIL->value,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => $meta ? (object) $meta : null,
        ], $statusCode);
    }

    public static function error(
        ?string $message = null,
        ?array $errors = null,
        ?array $meta = null,
        int $statusCode = 500
    ): JsonResponse {
        return response()->json([
            'status' => ApiResponseStatus::ERROR->value,
            'message' => $message,
            'data' => null,
            'errors' => $errors ? (object) $errors : null,
            'meta' => $meta ? (object) $meta : null,
        ], $statusCode);
    }

    public static function notFound(
        ?string $message = null,
        ?array $meta = null
    ): JsonResponse {
        return response()->json([
            'status' => ApiResponseStatus::ERROR->value,
            'message' => $message ?? 'Resource not found.',
            'data' => null,
            'errors' => null,
            'meta' => $meta ? (object) $meta : null,
        ], 404);
    }

    public static function unauthorized(
        ?string $message = null,
        ?array $meta = null
    ): JsonResponse {
        return response()->json([
            'status' => ApiResponseStatus::ERROR->value,
            'message' => $message ?? 'This action is unauthorized.',
            'data' => null,
            'errors' => null,
            'meta' => $meta ? (object) $meta : null,
        ], 403);
    }

    public static function validationError(
        ?string $message = null,
        ?array $errors = null,
        ?array $meta = null
    ): JsonResponse {
        return response()->json([
            'status' => ApiResponseStatus::FAIL->value,
            'message' => $message ?? 'Validation failed.',
            'data' => null,
            'errors' => $errors ? (object) $errors : null,
            'meta' => $meta ? (object) $meta : null,
        ], 422);
    }
}
