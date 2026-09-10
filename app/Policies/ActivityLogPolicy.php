<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    /**
     * Determine if the user can view any activity logs.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view activity logs
        // Filtering happens in the service layer based on role
        return true;
    }

    /**
     * Determine if the user can view the activity log.
     */
    public function view(User $user, ActivityLog $activityLog): bool
    {
        // Super Admin can view all logs
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Org Admin can view logs in their organizations
        if ($user->isOrgAdmin()) {
            // Check if the activity is related to their organization
            if ($activityLog->targetable_type === 'App\\Models\\Organization') {
                return $user->hasAccessToOrganization($activityLog->targetable);
            }

            // Check if activity is for a project in their organization
            if ($activityLog->targetable_type === 'App\\Models\\Project') {
                $organization = $activityLog->targetable?->organization;

                return $organization && $user->hasAccessToOrganization($organization);
            }

            // Check if it's their own activity
            return $activityLog->user_id === $user->id;
        }

        // PM can view logs in their assigned projects
        if ($user->isProjectManager()) {
            if ($activityLog->targetable_type === 'App\\Models\\Project') {
                return $user->hasAccessToProject($activityLog->targetable);
            }

            if ($activityLog->targetable_type === 'App\\Models\\Task') {
                $project = $activityLog->targetable?->project;

                return $project && $user->hasAccessToProject($project);
            }

            // Own activities
            return $activityLog->user_id === $user->id;
        }

        // Members can only view their own activity
        return $activityLog->user_id === $user->id;
    }
}
