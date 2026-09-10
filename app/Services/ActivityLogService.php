<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    /**
     * List activity logs with role-based filtering.
     *
     * - Super Admin: See all activity
     * - Org Admin: See activity in assigned organizations
     * - Project Manager: See activity in assigned projects
     * - Member: See only own security/session activity
     */
    public function list(
        User $user,
        int $perPage = 50,
        ?string $targetType = null,
        ?string $targetId = null,
        ?string $userId = null
    ): LengthAwarePaginator {
        $query = ActivityLog::query()->with(['user']);

        // Apply role-based access control
        if ($user->isSuperAdmin()) {
            // Super Admin: See all activity logs
            // No filtering needed
        } elseif ($user->isOrgAdmin()) {
            // Org Admin: See activity in assigned organizations
            $organizationIds = $user->organizations()
                ->wherePivot('status', 'active')
                ->pluck('organizations.id');

            $query->where(function ($q) use ($organizationIds, $user): void {
                // Organization-level activities
                $q->where(function ($subQ) use ($organizationIds): void {
                    $subQ->where('targetable_type', 'App\\Models\\Organization')
                        ->whereIn('targetable_id', $organizationIds);
                })
                // Project-level activities in their organizations
                    ->orWhereIn('targetable_id', function ($subQ) use ($organizationIds): void {
                        $subQ->select('id')
                            ->from('projects')
                            ->whereIn('organization_id', $organizationIds);
                    })
                // User activities in their organizations
                    ->orWhereIn('targetable_id', function ($subQ) use ($organizationIds): void {
                        $subQ->select('users.id')
                            ->from('users')
                            ->join('organization_user', 'users.id', '=', 'organization_user.user_id')
                            ->whereIn('organization_user.organization_id', $organizationIds);
                    })
                // Own activities
                    ->orWhere('user_id', $user->id);
            });
        } elseif ($user->isProjectManager()) {
            // PM: See activity in assigned projects only
            $projectIds = $user->projects()->pluck('projects.id');

            $query->where(function ($q) use ($projectIds, $user): void {
                // Project-level activities
                $q->where(function ($subQ) use ($projectIds): void {
                    $subQ->where('targetable_type', 'App\\Models\\Project')
                        ->whereIn('targetable_id', $projectIds);
                })
                // Task-level activities in their projects
                    ->orWhereIn('targetable_id', function ($subQ) use ($projectIds): void {
                        $subQ->select('id')
                            ->from('tasks')
                            ->whereIn('project_id', $projectIds);
                    })
                // User activities in their projects
                    ->orWhereIn('targetable_id', function ($subQ) use ($projectIds): void {
                        $subQ->select('users.id')
                            ->from('users')
                            ->join('project_user', 'users.id', '=', 'project_user.user_id')
                            ->whereIn('project_user.project_id', $projectIds);
                    })
                // Own activities
                    ->orWhere('user_id', $user->id);
            });
        } else {
            // Member: See only own security/session activity
            $query->where('user_id', $user->id);
        }

        // Apply additional filters
        if ($targetType !== null) {
            $query->where('targetable_type', $targetType);
        }

        if ($targetId !== null) {
            $query->where('targetable_id', $targetId);
        }

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Log an activity.
     */
    public function log(
        string $action,
        User $user,
        $targetable = null,
        ?string $description = null,
        ?array $metadata = null
    ): ActivityLog {
        return ActivityLog::create([
            'action' => $action,
            'user_id' => $user->id,
            'targetable_type' => $targetable ? get_class($targetable) : null,
            'targetable_id' => $targetable?->id,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
