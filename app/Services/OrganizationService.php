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
     * Assign user to organization.
     * User must be active (invitation accepted) before assignment.
     */
    public function assignUser(Organization $organization, User $user, User $assignedBy): bool
    {
        // Check if user is already assigned
        if ($organization->members()->where('users.id', $user->id)->exists()) {
            throw new \RuntimeException(__('organization.user_already_assigned'));
        }

        // Check if user is active
        if ($user->status !== UserStatus::ACTIVE->value) {
            throw new \RuntimeException(__('organization.user_must_be_active'));
        }

        return DB::transaction(function () use ($organization, $user, $assignedBy) {
            // Attach user to organization with active status
            $organization->members()->attach($user->id, [
                'status' => OrganizationUserStatus::ACTIVE->value,
                'invited_by' => $assignedBy->id,
                'invited_at' => now(),
                'accepted_at' => now(),
            ]);

            return true;
        });
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
}
