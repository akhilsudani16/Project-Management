<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Authenticate user and issue token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            email: $request->email,
            password: $request->password,
            ip: $request->ip(),
            deviceName: $request->device_name ?? $request->userAgent(),
        );

        return (new UserResource($result['user']))
            ->minimal()
            ->withMessage(__('auth.login_success'))
            ->withMeta(['token' => $result['token']])
            ->toResponse($request);
    }
}
