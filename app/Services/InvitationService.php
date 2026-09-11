<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrganizationUserStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\InvitationMail;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    /**
     * Unified invite method with manager-based assignment.
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
            // Step 1: Determine context based on inviter role and manager assignment
            $context = $this->determineInvitationContext(
                inviter: $inviter,
                role: $role,
                orgAdminId: $orgAdminId,
                projectManagerId: $projectManagerId
            );

            // Step 2: Validate permissions
            $this->validateInviterPermissions($inviter, $context);

            // Step 3: Find or create user
            $user = $this->findOrCreateUser(
                email: $email,
                name: $name,
                roleName: $context['role'],
                invitedBy: $inviter,
                assignedBy: $context['assigned_by']
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
                    invitedBy: $inviter,
                    assignedBy: $context['assigned_by']
                );
            }

            // Step 6: Generate invitation token and store in user table
            $invitationToken = Str::random(60);

            // Store hashed token directly in user table
            $user->update([
                'invitation_token' => hash('sha256', $invitationToken),
                'invitation_accepted_at' => null, // Reset if re-inviting
            ]);

            // Send invitation email
            Mail::to($user->email)->send(new InvitationMail(
                user: $user,
                organization: $context['organization'],
                project: $context['project'],
                token: $invitationToken, // Send plain token in email
                expiresAt: now()->addDays(7)->toISOString(),
                invitedBy: $inviter
            ));

            return [
                'user' => $user->fresh()->load('role'),
                'organization' => $context['organization'],
                'project' => $context['project'],
                'invitation' => [
                    'token' => $invitationToken,
                    'expires_at' => now()->addDays(7),
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
        ?string $orgAdminId,
        ?string $projectManagerId
    ): array {
        // Super Admin with Manager Assignment
        if ($inviter->isSuperAdmin()) {
            // Assign to Org Admin
            if ($orgAdminId !== null) {
                $orgAdmin = User::findOrFail($orgAdminId);

                if (! $orgAdmin->isOrgAdmin()) {
                    throw new \RuntimeException('Specified user is not an Organization Admin.');
                }

                $organization = $orgAdmin->organizations()
                    ->wherePivot('status', OrganizationUserStatus::ACTIVE->value)
                    ->first();

                if ($organization === null) {
                    throw new \RuntimeException('Organization Admin is not assigned to any active organization.');
                }

                return [
                    'role' => $role ?? UserRole::MEMBER->value,
                    'organization' => $organization,
                    'project' => null,
                    'assigned_by' => $orgAdmin->id,
                ];
            }

            // Assign to Project Manager
            if ($projectManagerId !== null) {
                $projectManager = User::findOrFail($projectManagerId);

                if (! $projectManager->isProjectManager()) {
                    throw new \RuntimeException('Specified user is not a Project Manager.');
                }

                $project = $projectManager->projects()->first();

                if ($project === null) {
                    throw new \RuntimeException('Project Manager is not assigned to any project.');
                }

                $organization = $project->organization;

                if ($organization === null) {
                    throw new \RuntimeException('Project does not belong to any organization.');
                }

                return [
                    'role' => UserRole::MEMBER->value, // Can only be member under PM
                    'organization' => $organization,
                    'project' => $project,
                    'assigned_by' => $projectManager->id,
                ];
            }

            throw new \RuntimeException('You must specify org_admin_id or project_manager_id.');
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

            // Assign to Project Manager
            if ($projectManagerId !== null) {
                $projectManager = User::findOrFail($projectManagerId);

                if (! $projectManager->isProjectManager()) {
                    throw new \RuntimeException('Specified user is not a Project Manager.');
                }

                // Verify PM belongs to same organization
                $pmOrg = $projectManager->organizations()
                    ->wherePivot('status', OrganizationUserStatus::ACTIVE->value)
                    ->first();

                if ($pmOrg === null || $pmOrg->id !== $organization->id) {
                    throw new \RuntimeException('Project Manager must belong to your organization.');
                }

                $project = $projectManager->projects()->first();

                return [
                    'role' => $role ?? UserRole::MEMBER->value,
                    'organization' => $organization,
                    'project' => $project,
                    'assigned_by' => $projectManager->id,
                ];
            }

            // Assign to Member (no specific manager)
            return [
                'role' => $role ?? UserRole::MEMBER->value,
                'organization' => $organization,
                'project' => null,
                'assigned_by' => $inviter->id,
            ];
        }

        // Project Manager
        if ($inviter->isProjectManager()) {
            // Auto-detect project
            $project = $inviter->projects()->first();

            if ($project === null) {
                throw new \RuntimeException('You are not assigned to any projects.');
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
                'assigned_by' => $inviter->id,
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
                'assigned_by' => $assignedBy, // Track who this user is assigned to
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
            /** @phpstan-ignore-next-line */
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
        User $invitedBy,
        ?string $assignedBy = null
    ): void {
        $existingAssignment = $project->users()
            ->where('users.id', $user->id)
            ->first();

        if ($existingAssignment !== null) {
            // Update existing assignment
            $project->users()->updateExistingPivot($user->id, [
                'assigned_by' => $assignedBy ?? $invitedBy->id,
                'assigned_at' => now(),
                'invitation_token' => Str::random(60),
                'invited_by' => $invitedBy->id,
                'invited_at' => now(),
            ]);
        } else {
            // Create new project assignment
            $project->users()->attach($user->id, [
                'id' => Str::uuid(),
                'assigned_by' => $assignedBy ?? $invitedBy->id,
                'assigned_at' => now(),
                'invitation_token' => Str::random(60),
                'invited_by' => $invitedBy->id,
                'invited_at' => now(),
            ]);
        }
    }
}
