<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
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
     */
    public function resend(Request $request): JsonResponse
    {
        $this->authService->resendVerification($request->user());

        return response()->json([
            'message' => __('verification.sent'),
        ]);
    }
}
