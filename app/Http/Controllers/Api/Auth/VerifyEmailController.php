<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Find user by email from query parameter
        $user = User::where('email', $request->query('email'))->first();

        if (! $user) {
            return ApiResponse::notFound(
                message: __('verification.user_not_found')
            );
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(
                message: __('verification.already_verified')
            );
        }

        // Mark as verified
        $user->markEmailAsVerified();

        return ApiResponse::success(
            message: __('verification.verified')
        );
    }
}
