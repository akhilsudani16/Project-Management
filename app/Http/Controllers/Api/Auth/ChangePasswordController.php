<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class ChangePasswordController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Change the authenticated user's password.
     */
    public function change(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->changePassword(
                user: $request->user(),
                currentPassword: $request->current_password,
                newPassword: $request->password,
            );

            return ApiResponse::success(
                message: __('passwords.changed'),
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
