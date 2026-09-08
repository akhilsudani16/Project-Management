<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;

class AuthService
{
    /**
     * Register a new user with Member role.
     */
    public function register(string $name, string $email, string $password): User
    {
        $memberRole = Role::where('name', UserRole::MEMBER->value)->firstOrFail();

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $memberRole->id,
        ]);
    }

    /**
     * Authenticate user and issue token.
     *
     * @return array{user: User, token: NewAccessToken}
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, string $ip, ?string $deviceName = null): array
    {
        // Rate limiting by email (3 attempts, 15 minutes block)
        $key = 'login.'.$email;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', ['seconds' => ceil($seconds / 60)])],
            ]);
        }

        // Find user and verify password
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            // Failed login - hit rate limiter
            RateLimiter::hit($key, 900); // 15 minutes

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // Success - clear rate limiter
        RateLimiter::clear($key);

        // Create access token
        $token = $user->createToken($deviceName ?? 'device');

        return [
            'user' => $user->load('role'),
            'token' => $token,
        ];
    }

    /**
     * Revoke current user token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Revoke all user tokens.
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Verify user email.
     *
     * @throws ValidationException
     */
    public function verifyEmail(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [__('verification.already_verified')],
            ]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    /**
     * Resend email verification notification.
     *
     * @throws ValidationException
     */
    public function resendVerification(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [__('verification.already_verified')],
            ]);
        }

        $user->sendEmailVerificationNotification();
    }

    /**
     * Change user password.
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        // Verify current password
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('passwords.current_incorrect')],
            ]);
        }

        // Check new password is different
        if (Hash::check($newPassword, $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('passwords.same_as_current')],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Revoke all other tokens for security
        $currentTokenId = $user->currentAccessToken()?->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();
    }

    /**
     * Send password reset link.
     *
     * @throws ValidationException
     */
    public function sendResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * Reset user password.
     *
     * @throws ValidationException
     */
    public function resetPassword(string $email, string $password, string $token): string
    {
        $status = Password::reset(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $password, 'token' => $token],
            function (User $user, string $password) {
                // Update password
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all tokens for security
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }

    /**
     * Get user's active sessions/tokens.
     */
    public function getActiveSessions(User $user): array
    {
        return $user->tokens()
            ->orderBy('last_used_at', 'desc')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
                'expires_at' => $token->expires_at,
                'is_current' => $token->id === $user->currentAccessToken()?->id,
            ])
            ->toArray();
    }

    /**
     * Revoke specific session/token.
     *
     * @throws ValidationException
     */
    public function revokeSession(User $user, string $tokenId): void
    {
        $token = $user->tokens()->find($tokenId);

        if (! $token) {
            throw ValidationException::withMessages([
                'token_id' => [__('auth.token_not_found')],
            ]);
        }

        $token->delete();
    }
}
