<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResendVerificationController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Resend the email verification notification.
     * Public endpoint - accepts email address.
     */
    public function resend(Request $request): JsonResponse
    {
        // Validate email
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        try {
            // Find user by email
            $user = User::where('email', $request->email)->firstOrFail();

            $this->authService->resendVerification($user);

            return ApiResponse::success(
                message: __('verification.sent'),
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
