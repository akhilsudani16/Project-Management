<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Carbon;
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
            'status' => UserStatus::PENDING->value,
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
        $key = 'login.'.$ip.'.'.$email;

        // Check rate limiting
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', ['seconds' => ceil($seconds / 60)])],
            ]);
        }

        // Find user
        $user = User::where('email', $email)->first();

        // Verify credentials
        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($key, 900); // 15 minutes lockout

            if ($user) {
                $this->handleFailedLogin($user);
            }

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // Check account status
        $this->checkAccountStatus($user);

        // Reset failed attempts
        $this->resetFailedAttempts($user);

        // Clear rate limiter
        RateLimiter::clear($key);

        // Create token
        $token = $user->createToken($deviceName ?? 'unknown', ['*'], now()->addDays(30));

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
     * Handle failed login attempt.
     */
    private function handleFailedLogin(User $user): void
    {
        $user->increment('failed_login_attempts');

        if ($user->failed_login_attempts >= 3) {
            $user->update([
                'lockout_until' => now()->addMinutes(15),
            ]);
        }
    }

    /**
     * Check if account is locked or suspended.
     *
     * @throws ValidationException
     */
    private function checkAccountStatus(User $user): void
    {
        // Check lockout
        $lockoutUntil = $user->lockout_until;
        if ($lockoutUntil instanceof Carbon && $lockoutUntil->isFuture()) {
            throw ValidationException::withMessages([
                'email' => [__('auth.locked')],
            ]);
        }

        // Check status
        $userStatus = $user->status;
        if ($userStatus instanceof UserStatus && in_array($userStatus->value, [UserStatus::SUSPENDED->value, UserStatus::INACTIVE->value], true)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.suspended')],
            ]);
        }
    }

    /**
     * Reset failed login attempts.
     */
    private function resetFailedAttempts(User $user): void
    {
        if ($user->failed_login_attempts > 0 || $user->lockout_until) {
            $user->update([
                'failed_login_attempts' => 0,
                'lockout_until' => null,
            ]);
        }
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
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('passwords.current_incorrect')],
            ]);
        }

        if (Hash::check($newPassword, $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('passwords.same_as_current')],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => false,
        ]);

        // Revoke all tokens except current
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
                $user->forceFill([
                    'password' => Hash::make($password),
                    'must_change_password' => false,
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all tokens
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
