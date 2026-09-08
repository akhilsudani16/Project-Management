<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Send password reset link.
     */
    public function send(ForgotPasswordRequest $request): JsonResponse
    {
        $message = $this->authService->sendResetLink($request->email);

        return response()->json([
            'message' => $message,
        ]);
    }
}
