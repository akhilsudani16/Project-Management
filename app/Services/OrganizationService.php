<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrganizationStatus;
use App\Enums\OrganizationUserStatus;
use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrganizationService
{
    /**
     * List organizations based on user access.
     */
    public function list(
        User $user,
        int $perPage = 15,
        ?string $status = null,
        ?string $search = null
    ): LengthAwarePaginator {
        $query = Organization::query()->with(['createdBy:id,name,email']);

        // Super Admins see all organizations
        if (! $user->isSuperAdmin()) {
            // Org Admins, PMs, and Members see organizations they belong to
            $organizationIds = $user->organizations()->pluck('organizations.id');
            $query->whereIn('id', $organizationIds);
        }

        // Apply filters
        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($search !== null) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get organization by ID with relationships.
     */
    public function getById(string $id): Organization
    {
        return Organization::with([
            'createdBy',
            'members' => function ($query): void {
                $query->wherePivot('status', OrganizationUserStatus::ACTIVE->value)
                    ->with(['role']);
            },
        ])
            ->withCount(['members', 'projects'])
            ->findOrFail($id);
    }

    /**
     * Create a new organization.
     */
    public function create(array $data, User $creator): Organization
    {
        return Organization::create([
            'name' => $data['name'],
            'status' => $data['status'] ?? OrganizationStatus::ACTIVE->value,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * Update an existing organization.
     * Only updates fields that are present in the request data.
     */
    public function update(Organization $organization, array $data): Organization
    {
        // Only update fields that are present in the request (exclude null values for optional fields)
        $allowedFields = ['name', 'status'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            // Check if field exists in data and has a non-null value
            if (array_key_exists($field, $data)) {
                // Include the field even if it's an empty string, but exclude null
                if ($data[$field] !== null) {
                    $updateData[$field] = $data[$field];
                }
            }
        }

        if (! empty($updateData)) {
            $organization->update($updateData);
        }

        return $organization->fresh();
    }

    /**
     * Soft delete (archive) an organization.
     */
    public function delete(Organization $organization, User $deletedBy): bool
    {
        // Check if organization has active projects
        if ($organization->projects()->whereNull('deleted_at')->count() > 0) {
            throw new \RuntimeException(__('organization.cannot_delete_with_active_projects'));
        }

        $organization->deleted_by = $deletedBy->id;
        $organization->save();

        return $organization->delete();
    }

    /**
     * Restore a soft-deleted organization.
     */
    public function restore(Organization $organization): bool
    {
        $organization->deleted_by = null;
        $organization->save();

        return $organization->restore();
    }

    /**
     * Get organization members.
     * - Super Admin and Org Admin: See all organization members
     * - Project Manager: See only members from their assigned projects
     * - Member: See only members from their assigned projects
     */
    public function getMembers(
        Organization $organization,
        User $user,
        int $perPage = 15,
        ?string $status = null,
        ?string $search = null
    ): LengthAwarePaginator {
        // Super Admin and Org Admin can see all members
        if ($user->isSuperAdmin() || $user->isOrgAdmin()) {
            $query = $organization->members()->with(['role']);

            if ($status !== null) {
                $query->wherePivot('status', $status);
            }

            if ($search !== null) {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%");
                });
            }

            return $query->withPivot(['status', 'invited_by', 'invited_at', 'accepted_at'])
                ->orderBy('organization_user.created_at', 'desc')
                ->paginate($perPage);
        }

        // Project Manager or Member: See only members from their assigned projects
        $projectIds = $user->projects()->pluck('projects.id');

        // Get unique user IDs from all projects the user is part of
        $userIds = \DB::table('project_user')
            ->whereIn('project_id', $projectIds)
            ->distinct()
            ->pluck('user_id');

        $query = $organization->members()
            ->with(['role'])
            ->whereIn('users.id', $userIds);

        if ($status !== null) {
            $query->wherePivot('status', $status);
        }

        if ($search !== null) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        return $query->withPivot(['status', 'invited_by', 'invited_at', 'accepted_at'])
            ->orderBy('organization_user.created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Update organization member role.
     */
    public function updateMemberRole(
        Organization $organization,
        User $user,
        string $newRoleName
    ): User {
        $role = Role::where('name', $newRoleName)->firstOrFail();

        DB::transaction(function () use ($user, $role): void {
            $user->update(['role_id' => $role->id]);
        });

        return $user->fresh()->load('role');
    }

    /**
     * Remove member from organization.
     */
    public function removeMember(Organization $organization, User $user, User $removedBy): bool
    {
        return DB::transaction(function () use ($organization, $user, $removedBy) {
            // Remove from all organization projects
            $projectIds = $organization->projects()->pluck('projects.id');
            $user->projects()->detach($projectIds); // Feature: if project alreday assised this user and remove same user so first assisge project other user and then remove user

            // Update pivot with deleted_by before detaching
            $organization->members()->updateExistingPivot($user->id, [
                'deleted_by' => $removedBy->id,
                'deleted_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Verify invitation token - Simple direct lookup.
     */
    public function verifyInvitationToken(string $token): array
    {
        $user = User::where('invitation_token', hash('sha256', $token))
            ->whereNull('invitation_accepted_at')
            ->first();

        if (! $user) {
            throw new \RuntimeException(__('organization.invalid_or_expired_invitation'));
        }

        // Get pending organization
        $organization = $user->organizations()
            ->wherePivot('status', OrganizationUserStatus::PENDING->value)
            ->first();

        if (! $organization) {
            throw new \RuntimeException(__('organization.no_pending_invitations'));
        }

        return [
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
            ],
            'expires_at' => now()->addDays(7)->toISOString(), // 7 days from creation
        ];
    }

    /**
     * Accept invitation and set password - Simple direct lookup.
     * Organization remains PENDING until assigned to project/org.
     */
    public function acceptInvitationWithPassword(
        string $token,
        string $password
    ): array {
        return DB::transaction(function () use ($token, $password) {
            // Step 1: Find user by token
            $user = User::where('invitation_token', hash('sha256', $token))
                ->whereNull('invitation_accepted_at')
                ->firstOrFail();

            // Step 2: Get pending organization
            $organization = $user->organizations()
                ->wherePivot('status', OrganizationUserStatus::PENDING->value)
                ->first();

            if (! $organization) {
                throw new \RuntimeException(__('organization.no_pending_invitations'));
            }

            // Step 3: Update user - set password and activate USER only
            $user->update([
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'status' => UserStatus::ACTIVE->value,
                'must_change_password' => false,
                'invitation_token' => null, // Clear token
                'invitation_accepted_at' => now(), // Mark as accepted
            ]);

            // Step 4: Update organization membership - mark invitation as accepted
            // BUT keep status as PENDING until project/org assignment
            $organization->members()->updateExistingPivot($user->id, [
                'accepted_at' => now(), // Mark as accepted
                // status remains PENDING - will be activated when assigned to project/org
            ]);

            return [
                'user' => $user->fresh()->load('role'),
                'organization' => $organization,
            ];
        });
    }

    /**
     * Accept organization invitation.
     */
    public function acceptInvitation(Organization $organization, User $user): bool
    {
        return DB::transaction(function () use ($organization, $user) {
            $organization->members()->updateExistingPivot($user->id, [
                'status' => OrganizationUserStatus::ACTIVE->value,
                'accepted_at' => now(),
            ]);

            // Update user status to active if pending
            if ($user->status === UserStatus::PENDING) {
                $user->update(['status' => UserStatus::ACTIVE]);
            }

            return true;
        });
    }

    /**
     * Reject organization invitation.
     */
    public function rejectInvitation(Organization $organization, User $user): bool
    {
        $organization->members()->updateExistingPivot($user->id, [
            'status' => OrganizationUserStatus::REJECTED->value,
        ]);

        return true;
    }
}
