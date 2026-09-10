<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine if the user can view any organizations.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can see organizations they have access to
        return true;
    }

    /**
     * Determine if the user can view the organization.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $user->hasAccessToOrganization($organization);
    }

    /**
     * Determine if the user can create organizations.
     */
    public function create(User $user): bool
    {
        // Only Super Admins can create organizations
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can update the organization.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->canManageOrganization($organization);
    }

    /**
     * Determine if the user can delete the organization.
     */
    public function delete(User $user, Organization $organization): bool
    {
        // Only Super Admins can delete organizations
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can restore the organization.
     */
    public function restore(User $user, Organization $organization): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine if the user can view organization members.
     */
    public function viewMembers(User $user, Organization $organization): bool
    {
        // Super Admin and Org Admin can see all organization members
        if ($user->isSuperAdmin() || $user->isOrgAdmin()) {
            return $user->hasAccessToOrganization($organization);
        }

        // Project Managers and Members can only see members if they belong to the organization
        // Note: The actual filtering of members happens in the service/controller layer
        // PMs should only see members from their assigned projects
        return $user->hasAccessToOrganization($organization);
    }

    /**
     * Determine if the user can invite members to the organization.
     */
    public function inviteMembers(User $user, Organization $organization, string $roleToInvite): bool
    {
        // Super Admins can invite anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Org Admins can invite PMs and Members, but not other Org Admins or Super Admins
        if ($user->isOrgAdmin() && $user->hasAccessToOrganization($organization)) {
            return in_array($roleToInvite, ['project_manager', 'member'], true);
        }

        return false;
    }

    /**
     * Determine if the user can update organization members.
     */
    public function updateMember(User $user, Organization $organization, User $targetUser): bool
    {
        // Cannot update yourself
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Super Admins can update anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Org Admins can update PMs and Members in their organization
        if ($user->isOrgAdmin() && $user->hasAccessToOrganization($organization)) {
            return ! $targetUser->isSuperAdmin() && ! $targetUser->isOrgAdmin();
        }

        return false;
    }

    /**
     * Determine if the user can remove members from the organization.
     */
    public function removeMember(User $user, Organization $organization, User $targetUser): bool
    {
        // Cannot remove yourself
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Super Admins can remove anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Org Admins can remove PMs and Members from their organization
        if ($user->isOrgAdmin() && $user->hasAccessToOrganization($organization)) {
            return ! $targetUser->isSuperAdmin() && ! $targetUser->isOrgAdmin();
        }

        return false;
    }
}
