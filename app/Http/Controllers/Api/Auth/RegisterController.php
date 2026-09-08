<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register(
            name: $request->name,
            email: $request->email,
            password: $request->password,
        );

        // Send email verification
        $user->sendEmailVerificationNotification();

        return (new UserResource($user))
            ->additional([
                'message' => __('auth.register_success'),
            ])
            ->response()
            ->setStatusCode(201);
    }
}
