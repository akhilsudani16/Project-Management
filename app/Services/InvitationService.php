<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\InvitationMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    /**
     * Simplified invite method - creates user and sends email.
     * Stores who this user is assigned under (org_admin or project_manager) for reference.
     * Does NOT assign to organization or project yet - that happens via separate APIs.
     *
     * Note: Validation is done in InviteRequest, not here.
     */
    public function invite(
        User $inviter,
        string $email,
        ?string $name = null,
        ?string $role = null,
        ?string $orgAdminId = null,
        ?string $projectManagerId = null
    ): array {
        return DB::transaction(function () use ($inviter, $email, $name, $role, $orgAdminId, $projectManagerId) {
            // Step 1: Determine who this user is assigned under
            $assignedBy = $this->determineAssignedBy($inviter, $orgAdminId, $projectManagerId);

            // Step 2: Find or create user
            $user = $this->findOrCreateUser(
                email: $email,
                name: $name,
                roleName: $role ?? UserRole::MEMBER->value, // Default to member if not provided
                invitedBy: $inviter,
                assignedBy: $assignedBy
            );

            // Step 3: Generate invitation token (hashed once)
            $invitationToken = hash('sha256', Str::random(60));

            // Store token in user table
            $user->update([
                'invitation_token' => $invitationToken,
                'invitation_accepted_at' => null, // Reset if re-inviting
            ]);

            // Step 4: Send invitation email (send same hashed token)
            Mail::to($user->email)->send(new InvitationMail(
                user: $user,
                organization: null, // Will be assigned later
                project: null, // Will be assigned later
                token: $invitationToken, // Send hashed token
                expiresAt: now()->addDays(7)->toISOString(),
                invitedBy: $inviter
            ));

            return [
                'user' => $user->fresh()->load('role'),
                'assigned_under' => $assignedBy, // Track who user is under
                'invitation' => [
                    'token' => $invitationToken,
                    'expires_at' => now()->addDays(7),
                ],
            ];
        });
    }

    /**
     * Verify invitation token is valid.
     * This is for UI to show user info before they accept.
     */
    public function verifyToken(string $token): array
    {
        $user = User::where('invitation_token', $token)
            ->whereNull('invitation_accepted_at')
            ->first();

        if (! $user) {
            throw new \RuntimeException(__('organization.invalid_or_expired_invitation'));
        }

        // Check if token is expired (7 days from user creation)
        if ($user->created_at->addDays(7)->isPast()) {
            throw new \RuntimeException(__('organization.invitation_expired'));
        }

        return [
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
            'expires_at' => $user->created_at->addDays(7)->toISOString(),
        ];
    }

    /**
     * Accept invitation and set password.
     * User becomes active after this.
     */
    public function acceptInvitation(string $token, string $password): array
    {
        return DB::transaction(function () use ($token, $password) {
            // Step 1: Find user by token
            $user = User::where('invitation_token', $token)
                ->whereNull('invitation_accepted_at')
                ->firstOrFail();

            // Step 2: Check if token is expired (7 days from user creation)
            if ($user->created_at->addDays(7)->isPast()) {
                throw new \RuntimeException(__('organization.invitation_expired'));
            }

            // Step 3: Update user - set password and activate USER
            $user->update([
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'status' => UserStatus::ACTIVE->value,
                'must_change_password' => false,
                'invitation_token' => null, // Clear token
                'invitation_accepted_at' => now(), // Mark as accepted
            ]);

            return [
                'user' => $user->fresh()->load('role'),
            ];
        });
    }

    /**
     * Determine who this user is assigned under (for tracking only, not actual assignment).
     */
    private function determineAssignedBy(User $inviter, ?string $orgAdminId, ?string $projectManagerId): ?string
    {
        // Super Admin explicitly provides who user is under
        if ($inviter->isSuperAdmin()) {
            if ($orgAdminId !== null) {
                $orgAdmin = User::findOrFail($orgAdminId);
                if (! $orgAdmin->isOrgAdmin()) {
                    throw new \RuntimeException('Specified user is not an Organization Admin.');
                }

                return $orgAdmin->id;
            }

            if ($projectManagerId !== null) {
                $pm = User::findOrFail($projectManagerId);
                if (! $pm->isProjectManager()) {
                    throw new \RuntimeException('Specified user is not a Project Manager.');
                }

                return $pm->id;
            }

            // No ID provided - user will be directly under Super Admin
            return $inviter->id;
        }

        // Org Admin invites - user can be under a PM or directly under Org Admin
        if ($inviter->isOrgAdmin()) {
            if ($projectManagerId !== null) {
                $pm = User::findOrFail($projectManagerId);
                if (! $pm->isProjectManager()) {
                    throw new \RuntimeException('Specified user is not a Project Manager.');
                }

                return $pm->id;
            }

            // User is directly under Org Admin
            return $inviter->id;
        }

        // PM invites - user is under the PM
        if ($inviter->isProjectManager()) {
            return $inviter->id;
        }
        throw new \RuntimeException('Specified user is not a valid inviter.');
    }

    /**
     * Find existing user or create new pending user.
     */
    private function findOrCreateUser(
        string $email,
        ?string $name,
        string $roleName,
        User $invitedBy,
        ?string $assignedBy = null
    ): User {
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $role = Role::where('name', $roleName)->firstOrFail();

            $user = User::create([
                'name' => $name ?? explode('@', $email)[0],
                'email' => $email,
                'role_id' => $role->id,
                'password' => Hash::make(Str::random(32)), // Random password
                'status' => UserStatus::PENDING->value,
                'must_change_password' => true,
                'created_by' => $invitedBy->id,
                'assigned_by' => $assignedBy, // Track who user is under
            ]);

            return $user;
        } else {
            throw new \RuntimeException(__('organization.user_already_active'));
        }
    }
}
