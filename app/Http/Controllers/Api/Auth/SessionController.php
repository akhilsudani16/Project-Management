<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Get all active sessions for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $sessions = $this->authService->getActiveSessions($request->user());

        return ApiResponse::success(
            data: ['sessions' => $sessions],
            message: 'Sessions retrieved successfully',
            statusCode: 200
        );
    }

    /**
     * Revoke a specific session.
     */
    public function destroy(Request $request, string $tokenId): JsonResponse
    {
        $this->authService->revokeSession($request->user(), $tokenId);

        return ApiResponse::success(
            message: __('auth.session_revoked'),
            statusCode: 200
        );
    }
}
