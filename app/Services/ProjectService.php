<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrganizationUserStatus;
use App\Enums\ProjectStatus;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * List projects based on user access.
     */
    public function list(
        User $user,
        int $perPage = 15,
        ?string $organizationId = null,
        ?string $status = null,
        ?string $search = null,
        ?bool $assignedToMe = null
    ): LengthAwarePaginator {
        $query = Project::query()->with(['organization']);

        // Filter by access
        if (! $user->isSuperAdmin()) {
            if ($user->isOrgAdmin()) {
                // Org Admins see projects in their organizations
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereIn('organization_id', $organizationIds);
            } else {
                // PMs and Members see only assigned projects
                $projectIds = $user->projects()->pluck('projects.id');
                $query->whereIn('id', $projectIds);
            }
        }

        // Apply filters
        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($assignedToMe === true) {
            $projectIds = $user->projects()->pluck('projects.id');
            $query->whereIn('id', $projectIds);
        }

        if ($search !== null) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        return $query->withCount(['tasks', 'members'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get project by ID with relationships.
     */
    public function getById(string $id): Project
    {
        return Project::with([
            'organization',
            'members' => function ($query): void {
                $query->with('role');
            },
        ])
            ->withCount(['tasks', 'members'])
            ->findOrFail($id);
    }

    /**
     * Create a new project.
     */
    public function create(array $data, User $creator): Project
    {
        // Verify organization access for non-super-admins
        if (! $creator->isSuperAdmin()) {
            $organization = Organization::findOrFail($data['organization_id']);
            if (! $creator->hasAccessToOrganization($organization)) {
                throw new \RuntimeException(__('project.no_access_to_organization'));
            }
        }

        return Project::create([
            'organization_id' => $data['organization_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? ProjectStatus::ACTIVE->value,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * Update an existing project.
     * Only updates fields that are present in the request data.
     *
     * Restrictions:
     * - PM cannot change organization_id
     * - PM cannot change ownership or sensitive fields
     */
    public function update(Project $project, array $data, User $updater): Project
    {
        // Prevent non-admins from changing organization
        if (isset($data['organization_id']) && $data['organization_id'] !== $project->organization_id) {
            if (! $updater->isSuperAdmin() && ! $updater->isOrgAdmin()) {
                throw new \RuntimeException(__('project.cannot_change_organization'));
            }
        }

        // Define allowed fields based on user role
        if ($updater->isProjectManager()) {
            // PM can only update: name, description, status, dates
            $allowedFields = ['name', 'description', 'status', 'start_date', 'end_date'];
        } else {
            // Super Admin and Org Admin can update all fields
            $allowedFields = ['organization_id', 'name', 'description', 'status', 'start_date', 'end_date'];
        }

        // Only update fields that exist in the request and are allowed for user role
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        $project->update($updateData);

        return $project->fresh();
    }

    /**
     * Soft delete (archive) a project.
     */
    public function delete(Project $project, User $deletedBy): bool
    {
        $project->deleted_by = $deletedBy->id;
        $project->save();

        return $project->delete();
    }

    /**
     * Restore a soft-deleted project.
     */
    public function restore(Project $project): bool
    {
        $project->deleted_by = null;
        $project->save();

        return $project->restore();
    }

    /**
     * Get project members.
     */
    public function getMembers(Project $project, int $perPage = 15): LengthAwarePaginator
    {
        return $project->members()
            ->with('role')
            ->withPivot(['assigned_by', 'assigned_at'])
            ->paginate($perPage);
    }

    /**
     * Assign user to project.
     * Also activates organization membership if pending.
     */
    public function assignUser(Project $project, User $user, User $assignedBy): bool
    {
        $organization = $project->organization;
        if (! $organization instanceof Organization) {
            throw new \RuntimeException(__('project.invalid_organization'));
        }

        // Verify user is in the organization
        if (! $user->hasAccessToOrganization($organization)) {
            throw new \RuntimeException(__('project.user_not_in_organization'));
        }

        // Check if already assigned
        if ($project->members()->where('users.id', $user->id)->exists()) {
            throw new \RuntimeException(__('project.user_already_assigned'));
        }

        $project->members()->attach($user->id, [
            'id' => Str::uuid(),
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
            'invitation_token' => Str::random(60), // Generate token even for direct assignment
            'invited_by' => $assignedBy->id, // Same as assigned_by for direct assignment
            'invited_at' => now(),
            'accepted_at' => now(), // Auto-accept for direct assignment
        ]);

        // Activate organization membership if still pending
        $pivotData = $organization->members()->where('users.id', $user->id)->first();
        if ($pivotData && $pivotData->pivot->status === OrganizationUserStatus::PENDING->value) {
            $organization->members()->updateExistingPivot($user->id, [
                'status' => OrganizationUserStatus::ACTIVE->value,
            ]);
        }

        return true;
    }

    /**
     * Remove user from project.
     */
    public function removeUser(Project $project, User $user): bool
    {
        // Check if user has assigned tasks
        $assignedTasksCount = $project->tasks()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->count();

        if ($assignedTasksCount > 0) {
            throw new \RuntimeException(__('project.user_has_assigned_tasks'));
        }

        return (bool) $project->members()->detach($user->id);
    }

    /**
     * Calculate project progress.
     */
    public function calculateProgress(Project $project): int
    {
        $totalTasks = $project->tasks()->count();

        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $project->tasks()
            ->where('status', 'completed')
            ->count();

        return (int) round(($completedTasks / $totalTasks) * 100);
    }
}
