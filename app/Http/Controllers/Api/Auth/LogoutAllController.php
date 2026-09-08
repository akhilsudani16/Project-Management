<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutAllController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Revoke all user tokens.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return response()->json([
            'message' => __('auth.logout_all_success'),
        ]);
    }
}
