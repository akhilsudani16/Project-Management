<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrganizationUserStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InvitationService
{
    /**
     * Unified invite method with smart context-aware logic.
     */
    public function invite(
        User $inviter,
        string $email,
        ?string $name = null,
        ?string $role = null,
        ?string $organizationId = null,
        ?string $projectId = null
    ): array {
        return DB::transaction(function () use ($inviter, $email, $name, $role, $organizationId, $projectId) {
            // Step 1: Determine context based on inviter role
            $context = $this->determineInvitationContext($inviter, $role, $organizationId, $projectId);

            // Step 2: Validate permissions
            $this->validateInviterPermissions($inviter, $context);

            // Step 3: Find or create user
            $user = $this->findOrCreateUser(
                email: $email,
                name: $name,
                roleName: $context['role'],
                invitedBy: $inviter
            );

            // Step 4: Add to organization
            $this->addToOrganization(
                user: $user,
                organization: $context['organization'],
                invitedBy: $inviter
            );

            // Step 5: Add to project (if specified)
            if ($context['project'] !== null) {
                $this->addToProject(
                    user: $user,
                    project: $context['project'],
                    invitedBy: $inviter
                );
            }

            // Step 6: Generate invitation token
            $invitationToken = Str::random(60);
            $expiresAt = now()->addDays(7);

            // TODO: Send invitation email
            // Mail::to($user->email)->send(new UserInvitation(...));

            return [
                'user' => $user->fresh()->load('role'),
                'organization' => $context['organization'],
                'project' => $context['project'],
                'invitation' => [
                    'token' => $invitationToken,
                    'expires_at' => $expiresAt,
                ],
            ];
        });
    }

    /**
     * Determine invitation context based on inviter role.
     */
    private function determineInvitationContext(
        User $inviter,
        ?string $role,
        ?string $organizationId,
        ?string $projectId
    ): array {
        // Super Admin
        if ($inviter->isSuperAdmin()) {
            $organization = Organization::findOrFail($organizationId);
            $project = $projectId !== null ? Project::findOrFail($projectId) : null;

            return [
                'role' => $role,
                'organization' => $organization,
                'project' => $project,
            ];
        }

        // Organization Admin
        if ($inviter->isOrgAdmin()) {
            // Auto-detect organization from inviter's membership
            $organization = $inviter->organizations()
                ->wherePivot('status', OrganizationUserStatus::ACTIVE->value)
                ->first();

            if ($organization === null) {
                throw new \RuntimeException('You are not assigned to any active organization.');
            }

            $project = null;
            if ($projectId !== null) {
                $project = Project::where('id', $projectId)
                    ->where('organization_id', $organization->id)
                    ->firstOrFail();
            }

            return [
                'role' => $role,
                'organization' => $organization,
                'project' => $project,
            ];
        }

        // Project Manager
        if ($inviter->isProjectManager()) {
            // Auto-detect project
            if ($projectId !== null) {
                $project = $inviter->projects()->findOrFail($projectId);
            } else {
                $projectsCount = $inviter->projects()->count();

                if ($projectsCount === 0) {
                    throw new \RuntimeException('You are not assigned to any projects.');
                }

                if ($projectsCount > 1) {
                    throw new \RuntimeException('You must specify a project_id when you manage multiple projects.');
                }

                $project = $inviter->projects()->first();
            }

            /** @var Organization|null $organization */
            $organization = $project->organization;

            if ($organization === null) {
                throw new \RuntimeException('Project does not belong to any organization.');
            }

            return [
                'role' => UserRole::MEMBER->value, // PM can only invite Members
                'organization' => $organization,
                'project' => $project,
            ];
        }

        throw new \RuntimeException('You are not authorized to invite users.');
    }

    /**
     * Validate inviter permissions.
     */
    private function validateInviterPermissions(User $inviter, array $context): void
    {
        // Super Admin can invite anyone
        if ($inviter->isSuperAdmin()) {
            return;
        }

        // Org Admin can only invite PM or Member
        if ($inviter->isOrgAdmin()) {
            if (! in_array($context['role'], [UserRole::PROJECT_MANAGER->value, UserRole::MEMBER->value], true)) {
                throw new \RuntimeException('Organization Admins can only invite Project Managers or Members.');
            }

            // Verify org admin has access to this organization
            if (! $inviter->hasAccessToOrganization($context['organization'])) {
                throw new \RuntimeException('You do not have access to this organization.');
            }

            return;
        }

        // PM can only invite Member
        if ($inviter->isProjectManager()) {
            if ($context['role'] !== UserRole::MEMBER->value) {
                throw new \RuntimeException('Project Managers can only invite Members.');
            }

            // Verify PM has access to this project
            if ($context['project'] !== null && ! $inviter->hasAccessToProject($context['project'])) {
                throw new \RuntimeException('You do not have access to this project.');
            }

            return;
        }

        throw new \RuntimeException('You are not authorized to invite users.');
    }

    /**
     * Find existing user or create new pending user.
     */
    private function findOrCreateUser(
        string $email,
        ?string $name,
        string $roleName,
        User $invitedBy
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
            ]);
        }

        return $user;
    }

    /**
     * Add user to organization.
     */
    private function addToOrganization(
        User $user,
        Organization $organization,
        User $invitedBy
    ): void {
        $existingMembership = $organization->members()
            ->where('users.id', $user->id)
            ->first();

        if ($existingMembership !== null) {
            $pivotStatus = $existingMembership->pivot->status;

            if ($pivotStatus === OrganizationUserStatus::ACTIVE->value) {
                throw new \RuntimeException('User is already an active member of this organization.');
            }

            // Resend invitation if pending or rejected
            $organization->members()->updateExistingPivot($user->id, [
                'status' => OrganizationUserStatus::PENDING->value,
                'invited_by' => $invitedBy->id,
                'invited_at' => now(),
                'accepted_at' => null,
            ]);
        } else {
            // Create new organization membership
            $organization->members()->attach($user->id, [
                'id' => Str::uuid(),
                'status' => OrganizationUserStatus::PENDING->value,
                'invited_by' => $invitedBy->id,
                'invited_at' => now(),
            ]);
        }
    }

    /**
     * Add user to project.
     */
    private function addToProject(
        User $user,
        Project $project,
        User $invitedBy
    ): void {
        $existingAssignment = $project->users()
            ->where('users.id', $user->id)
            ->first();

        if ($existingAssignment !== null) {
            // Update existing assignment
            $project->users()->updateExistingPivot($user->id, [
                'assigned_by' => $invitedBy->id,
                'assigned_at' => now(),
            ]);
        } else {
            // Create new project assignment
            $project->users()->attach($user->id, [
                'id' => Str::uuid(),
                'assigned_by' => $invitedBy->id,
                'assigned_at' => now(),
            ]);
        }
    }
}
