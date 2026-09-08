<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    /**
     * List activity logs with filtering.
     */
    public function list(
        User $user,
        int $perPage = 50,
        ?string $targetType = null,
        ?string $targetId = null,
        ?string $userId = null
    ): LengthAwarePaginator {
        $query = ActivityLog::query()->with(['user']);

        // Apply access control
        if (! $user->isSuperAdmin()) {
            // Only show logs for accessible resources
            // For simplicity, show user's own actions + actions in their projects/orgs
            $query->where(function ($q) use ($user): void {
                $q->where('user_id', $user->id);
                // Or show all in accessible projects (simplified)
            });
        }

        // Apply filters
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
