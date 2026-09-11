<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Any authenticated user can view tags in their organization
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tag $tag): bool
    {
        // Super admins can view all tags
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User must be member of the tag's organization
        return $user->hasAccessToOrganization($tag->organization);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Org admins and project managers can create tags
        return $user->isSuperAdmin() || $user->isOrgAdmin() || $user->isProjectManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tag $tag): bool
    {
        // Super admins can update any tag
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Org admins and project managers can update tags in their organization
        if ($user->isOrgAdmin() || $user->isProjectManager()) {
            return $user->hasAccessToOrganization($tag->organization);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tag $tag): bool
    {
        // Super admins can delete any tag
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Org admins can delete tags in their organization
        if ($user->isOrgAdmin()) {
            return $user->hasAccessToOrganization($tag->organization);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tag $tag): bool
    {
        return $this->delete($user, $tag);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tag $tag): bool
    {
        return $this->delete($user, $tag);
    }
}
