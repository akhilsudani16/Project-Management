<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

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
        $this->authService->changePassword(
            user: $request->user(),
            currentPassword: $request->current_password,
            newPassword: $request->password,
        );

        return response()->json([
            'message' => __('passwords.changed'),
        ]);
    }
}
