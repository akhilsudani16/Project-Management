<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine if the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can see projects they have access to
        return true;
    }

    /**
     * Determine if the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->hasAccessToProject($project);
    }

    /**
     * Determine if the user can create projects.
     */
    public function create(User $user): bool
    {
        // Super Admins and Org Admins can create projects
        return $user->isSuperAdmin() || $user->isOrgAdmin();
    }

    /**
     * Determine if the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->canManageProject($project);
    }

    /**
     * Determine if the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only Super Admins and Org Admins can delete projects
        if ($user->isSuperAdmin()) {
            return true;
        }

        $organization = $project->organization;
        if ($user->isOrgAdmin() && $organization instanceof Organization && $user->hasAccessToOrganization($organization)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    /**
     * Determine if the user can view project members.
     */
    public function viewMembers(User $user, Project $project): bool
    {
        return $user->hasAccessToProject($project);
    }

    /**
     * Determine if the user can assign members to the project.
     */
    public function assignMembers(User $user, Project $project): bool
    {
        return $user->canManageProject($project);
    }

    /**
     * Determine if the user can remove members from the project.
     */
    public function removeMembers(User $user, Project $project): bool
    {
        return $user->canManageProject($project);
    }
}
