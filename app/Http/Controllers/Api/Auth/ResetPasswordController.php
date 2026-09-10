<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class ResetPasswordController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Reset the user's password.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $message = $this->authService->resetPassword(
                email: $request->email,
                password: $request->password,
                token: $request->token,
            );

            return ApiResponse::success(
                message: $message,
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                statusCode: 422
            );
        }
    }
}
