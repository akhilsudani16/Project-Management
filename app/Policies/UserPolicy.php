<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can list users
        return true;
    }

    /**
     * Determine if the user can view the user.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super Admins can view anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Org Admins can view users in their organizations
        if ($user->isOrgAdmin()) {
            $sharedOrgs = $user->organizations()
                ->whereIn('organizations.id', $model->organizations()->pluck('organizations.id'))
                ->exists();

            if ($sharedOrgs) {
                return true;
            }
        }

        // PMs can view users in their projects
        $sharedProjects = $user->projects()
            ->whereIn('projects.id', $model->projects()->pluck('projects.id'))
            ->exists();

        return $sharedProjects;
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        // Only Super Admins can directly create users
        // Others can invite users through organizations
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Super Admins can update anyone
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Only Super Admins can delete users
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can restore the user.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can change role of another user.
     */
    public function changeRole(User $user, User $model): bool
    {
        // Cannot change your own role
        if ($user->id === $model->id) {
            return false;
        }

        // Only Super Admins can change roles
        return $user->isSuperAdmin();
    }
}
