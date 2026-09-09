<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
            return response()->json([
                'message' => 'User not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        // Mark as verified
        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email verified successfully. You can now login.',
        ]);
    }
}
