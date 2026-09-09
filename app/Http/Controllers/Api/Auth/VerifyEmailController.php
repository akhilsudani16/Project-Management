<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        // Find the user
        $user = User::findOrFail($id);

        // Verify the hash matches
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link.',
            ], 403);
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
