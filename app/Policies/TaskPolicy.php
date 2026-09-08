<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine if the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view tasks
        return true;
    }

    /**
     * Determine if the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        $project = $task->project;

        return $project instanceof Project && $user->hasAccessToProject($project);
    }

    /**
     * Determine if the user can create tasks.
     */
    public function create(User $user): bool
    {
        // All users can create tasks in projects they have access to
        // Project access check will be done at controller level
        return true;
    }

    /**
     * Determine if the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        $project = $task->project;
        if (! $project instanceof Project) {
            return false;
        }

        // Super Admins and Org Admins can update any task in accessible projects
        if ($user->isSuperAdmin()) {
            return true;
        }

        $organization = $project->organization;
        if ($user->isOrgAdmin() && $organization instanceof Organization && $user->hasAccessToOrganization($organization)) {
            return true;
        }

        // Project Managers can update tasks in their projects
        if ($user->isProjectManager() && $user->hasAccessToProject($project)) {
            return true;
        }

        // Members can only update tasks assigned to them (limited fields)
        if ($user->isMember() && $task->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        $project = $task->project;
        if (! $project instanceof Project) {
            return false;
        }

        // Only Super Admins, Org Admins, and Project Managers can delete tasks
        if ($user->isSuperAdmin()) {
            return true;
        }

        $organization = $project->organization;
        if ($user->isOrgAdmin() && $organization instanceof Organization && $user->hasAccessToOrganization($organization)) {
            return true;
        }

        if ($user->isProjectManager() && $user->hasAccessToProject($project)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can restore the task.
     */
    public function restore(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }

    /**
     * Determine if the user can update all task fields.
     */
    public function updateAllFields(User $user, Task $task): bool
    {
        // Members can only update limited fields (status, progress)
        if ($user->isMember()) {
            return false;
        }

        return $this->update($user, $task);
    }

    /**
     * Determine if the user can assign the task to other users.
     */
    public function assign(User $user, Task $task): bool
    {
        $project = $task->project;

        return $project instanceof Project && $user->canManageProject($project);
    }
}
