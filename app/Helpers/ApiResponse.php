<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\ApiResponseStatus;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public static function success(
        ?string $message = null,
        mixed $data = null,
        ?array $meta = null,
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        return self::respond(
            status: ApiResponseStatus::SUCCESS,
            message: $message,
            data: $data,
            errors: null,
            meta: $meta,
            statusCode: $statusCode
        );
    }

    public static function created(
        ?string $message = null,
        mixed $data = null,
        ?array $meta = null
    ): JsonResponse {
        return self::success(
            message: $message,
            data: $data,
            meta: $meta,
            statusCode: Response::HTTP_CREATED
        );
    }

    public static function error(
        ?string $message = null,
        ?array $errors = null,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        ?array $meta = null
    ): JsonResponse {
        return self::respond(
            status: ApiResponseStatus::ERROR,
            message: $message,
            data: null,
            errors: $errors,
            meta: $meta,
            statusCode: $statusCode
        );
    }

    public static function unauthorized(?string $message = 'Unauthorized', ?array $meta = null): JsonResponse
    {
        return self::error(message: $message, statusCode: Response::HTTP_UNAUTHORIZED, meta: $meta);
    }

    public static function forbidden(?string $message = 'Forbidden', ?array $meta = null): JsonResponse
    {
        return self::error(message: $message, statusCode: Response::HTTP_FORBIDDEN, meta: $meta);
    }

    public static function notFound(?string $message = 'Resource not found', ?array $meta = null): JsonResponse
    {
        return self::error(message: $message, statusCode: Response::HTTP_NOT_FOUND, meta: $meta);
    }

    public static function validationError(
        ?string $message = 'Validation failed',
        ?array $errors = null,
        ?array $meta = null
    ): JsonResponse {
        return self::error(
            message: $message,
            errors: $errors,
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            meta: $meta
        );
    }

    public static function fail(
        ?string $message = 'An unexpected error occurred',
        ?array $errors = null,
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        return self::respond(
            status: ApiResponseStatus::FAIL,
            message: $message,
            data: null,
            errors: $errors,
            meta: null,
            statusCode: $statusCode
        );
    }

    private static function respond(
        ApiResponseStatus $status,
        ?string $message,
        mixed $data,
        ?array $errors,
        ?array $meta,
        int $statusCode
    ): JsonResponse {
        return response()->json([
            'status' => $status->value,
            'message' => $message,
            'data' => $data,
            'errors' => $errors ? (object) $errors : null,
            'meta' => $meta ? (object) $meta : null,
        ], $statusCode);
    }
}
