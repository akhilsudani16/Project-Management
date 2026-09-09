<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrganizationStatus;
use App\Enums\OrganizationUserStatus;
use App\Enums\UserStatus;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $query = Organization::query()->with(['createdBy']);

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

        return $query->withCount(['members', 'projects'])
            ->orderBy('created_at', 'desc')
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
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? OrganizationStatus::ACTIVE->value,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * Update an existing organization.
     */
    public function update(Organization $organization, array $data): Organization
    {
        $organization->update(array_filter([
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn ($value) => $value !== null));

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
     */
    public function getMembers(
        Organization $organization,
        int $perPage = 15,
        ?string $status = null,
        ?string $search = null
    ): LengthAwarePaginator {
        $query = $organization->members()
            ->with(['role']);

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
     * Invite a user to the organization.
     */
    public function inviteUser(
        Organization $organization,
        string $email,
        string $roleName,
        User $invitedBy,
        ?string $name = null
    ): array {
        return DB::transaction(function () use ($organization, $email, $roleName, $invitedBy, $name) {
            // Find or create user
            $user = User::where('email', $email)->first();

            if ($user === null) {
                // Create new pending user
                $role = Role::where('name', $roleName)->firstOrFail();

                $user = User::create([
                    'name' => $name ?? explode('@', $email)[0],
                    'email' => $email,
                    'role_id' => $role->id,
                    'password' => Hash::make(Str::random(32)), // Random password
                    'status' => UserStatus::PENDING->value,
                    'must_change_password' => true,
                    'created_by' => $invitedBy->id,
                ]);
            }

            // Check if already a member
            $existingMembership = $organization->members()
                ->where('users.id', $user->id)
                ->first();

            if ($existingMembership !== null) {
                /** @var OrganizationUser $pivotModel */
                $pivotModel = $existingMembership->pivot;
                $pivotStatus = $pivotModel->status;

                if ($pivotStatus === OrganizationUserStatus::ACTIVE->value) {
                    throw new \RuntimeException(__('organization.user_already_member'));
                }

                // Resend invitation if pending or rejected
                $organization->members()->updateExistingPivot($user->id, [
                    'status' => OrganizationUserStatus::PENDING->value,
                    'invited_by' => $invitedBy->id,
                    'invited_at' => now(),
                    'accepted_at' => null,
                ]);
            } else {
                // Create new organization membership
                $organization->members()->attach($user->id, [
                    'id' => Str::uuid(),
                    'status' => OrganizationUserStatus::PENDING->value,
                    'invited_by' => $invitedBy->id,
                    'invited_at' => now(),
                ]);
            }

            // Generate invitation token
            $invitationToken = Str::random(60);
            $expiresAt = now()->addDays(7);

            // Store invitation token in cache (7 days expiry)
            $cacheKey = "invitation:{$user->email}:{$organization->id}";
            Cache::put($cacheKey, [
                'token' => hash('sha256', $invitationToken), // Store hashed token
                'organization_id' => $organization->id,
                'email' => $user->email,
                'expires_at' => $expiresAt,
            ], $expiresAt);

            // TODO: Send invitation email with token
            // Mail::to($user->email)->send(new OrganizationInvitation($organization, $invitationToken, $expiresAt));

            return [
                'user' => $user->fresh()->load('role'),
                'invitation' => [
                    'token' => $invitationToken,
                    'expires_at' => $expiresAt,
                ],
            ];
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
            $user->projects()->detach($projectIds);

            // Update pivot with deleted_by before detaching
            $organization->members()->updateExistingPivot($user->id, [
                'deleted_by' => $removedBy->id,
                'deleted_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Accept organization invitation with password setup.
     */
    public function acceptInvitationWithPassword(
        string $email,
        string $token,
        string $password
    ): array {
        return DB::transaction(function () use ($email, $token, $password) {
            // Find user
            $user = User::where('email', $email)->firstOrFail();

            // Get all pending organization memberships for this user
            $organizations = Organization::whereHas('members', function ($query) use ($user): void {
                $query->where('users.id', $user->id)
                    ->where('organization_user.status', OrganizationUserStatus::PENDING->value);
            })->get();

            if ($organizations->isEmpty()) {
                throw new \RuntimeException(__('organization.no_pending_invitations'));
            }

            // Validate token against any of the organizations
            $validOrganization = null;
            foreach ($organizations as $org) {
                $cacheKey = "invitation:{$email}:{$org->id}";
                $cachedData = Cache::get($cacheKey);

                if ($cachedData && hash('sha256', $token) === $cachedData['token']) {
                    $validOrganization = $org;
                    Cache::forget($cacheKey); // Remove used token
                    break;
                }
            }

            if ($validOrganization === null) {
                throw new \RuntimeException(__('organization.invalid_or_expired_invitation'));
            }

            // Update user password and status
            $user->update([
                'password' => Hash::make($password),
                'status' => UserStatus::ACTIVE->value,
                'must_change_password' => false,
                'email_verified_at' => now(), // Auto-verify email on invitation acceptance
            ]);

            // Activate organization membership
            $validOrganization->members()->updateExistingPivot($user->id, [
                'status' => OrganizationUserStatus::ACTIVE->value,
                'accepted_at' => now(),
            ]);

            return [
                'user' => $user->fresh()->load('role'),
                'organization' => $validOrganization,
            ];
        });
    }

    /**
     * Accept organization invitation.
     */
    public function acceptInvitation(Organization $organization, User $user): bool
    {
        return DB::transaction(function () use ($organization, $user) {
            $organization->members()->updateExistingPivot($user->id, [
                'status' => OrganizationUserStatus::ACTIVE->value,
                'accepted_at' => now(),
            ]);

            // Update user status to active if pending
            if ($user->status === UserStatus::PENDING) {
                $user->update(['status' => UserStatus::ACTIVE]);
            }

            return true;
        });
    }

    /**
     * Reject organization invitation.
     */
    public function rejectInvitation(Organization $organization, User $user): bool
    {
        $organization->members()->updateExistingPivot($user->id, [
            'status' => OrganizationUserStatus::REJECTED->value,
        ]);

        return true;
    }
}
