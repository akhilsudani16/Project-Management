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
use Carbon\Carbon;
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
            'status' => $data['status'] ?? OrganizationStatus::ACTIVE->value,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * Update an existing organization.
     * Only updates fields that are present in the request data.
     */
    public function update(Organization $organization, array $data): Organization
    {
        // Only update fields that are present in the request (exclude null values for optional fields)
        $allowedFields = ['name', 'status'];
        $updateData = [];

        foreach ($allowedFields as $field) {
            // Check if field exists in data and has a non-null value
            if (array_key_exists($field, $data)) {
                // Include the field even if it's an empty string, but exclude null
                if ($data[$field] !== null) {
                    $updateData[$field] = $data[$field];
                }
            }
        }

        if (! empty($updateData)) {
            $organization->update($updateData);
        }

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
     * - Super Admin and Org Admin: See all organization members
     * - Project Manager: See only members from their assigned projects
     * - Member: See only members from their assigned projects
     */
    public function getMembers(
        Organization $organization,
        User $user,
        int $perPage = 15,
        ?string $status = null,
        ?string $search = null
    ): LengthAwarePaginator {
        // Super Admin and Org Admin can see all members
        if ($user->isSuperAdmin() || $user->isOrgAdmin()) {
            $query = $organization->members()->with(['role']);

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

        // Project Manager or Member: See only members from their assigned projects
        $projectIds = $user->projects()->pluck('projects.id');

        // Get unique user IDs from all projects the user is part of
        $userIds = \DB::table('project_user')
            ->whereIn('project_id', $projectIds)
            ->distinct()
            ->pluck('user_id');

        $query = $organization->members()
            ->with(['role'])
            ->whereIn('users.id', $userIds);

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
            $user->projects()->detach($projectIds); // Feature: if project alreday assised this user and remove same user so first assisge project other user and then remove user

            // Update pivot with deleted_by before detaching
            $organization->members()->updateExistingPivot($user->id, [
                'deleted_by' => $removedBy->id,
                'deleted_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Get email from invitation token by searching cache.
     */
    private function getEmailFromToken(string $token): ?string
    {
        $hashedToken = hash('sha256', $token);

        // Search through all users with pending invitations
        $pendingUsers = User::whereIn('id', function ($query) {
            $query->select('user_id')
                ->from('organization_user')
                ->where('status', OrganizationUserStatus::PENDING->value);
        })->get();

        foreach ($pendingUsers as $user) {
            // Get organizations for this user
            $organizationIds = \DB::table('organization_user')
                ->where('user_id', $user->id)
                ->where('status', OrganizationUserStatus::PENDING->value)
                ->pluck('organization_id');

            $organizations = Organization::whereIn('id', $organizationIds)->get();

            // Check cache for each organization
            foreach ($organizations as $org) {
                $cacheKey = "invitation:{$user->email}:{$org->id}";
                $cachedData = Cache::get($cacheKey);

                if ($cachedData && $hashedToken === $cachedData['token']) {
                    return $user->email;
                }
            }
        }

        return null;
    }

    /**
     * Verify invitation token and return user/organization info.
     * This allows the UI to validate the token before password setup.
     */
    public function verifyInvitationToken(string $token): array
    {
        // Get email from token
        $email = $this->getEmailFromToken($token);

        if (! $email) {
            throw new \RuntimeException(__('organization.invalid_or_expired_invitation'));
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw new \RuntimeException(__('organization.user_not_found'));
        }

        // Get all pending organization memberships for this user
        $organizationIds = \DB::table('organization_user')
            ->where('user_id', $user->id)
            ->where('status', OrganizationUserStatus::PENDING->value)
            ->pluck('organization_id');

        $organizations = Organization::whereIn('id', $organizationIds)->get();

        if ($organizations->isEmpty()) {
            throw new \RuntimeException(__('organization.no_pending_invitations'));
        }

        // Validate token against any of the organizations
        $validOrganization = null;
        $expiresAt = null;

        foreach ($organizations as $org) {
            $cacheKey = "invitation:{$email}:{$org->id}";
            $cachedData = Cache::get($cacheKey);

            if ($cachedData && hash('sha256', $token) === $cachedData['token']) {
                // Check expiration (parse string back to Carbon)
                $expirationDate = Carbon::parse($cachedData['expires_at']);
                if (now()->greaterThan($expirationDate)) {
                    throw new \RuntimeException(__('organization.invitation_expired'));
                }

                $validOrganization = $org;
                $expiresAt = $cachedData['expires_at'];
                break;
            }
        }

        if ($validOrganization === null) {
            throw new \RuntimeException(__('organization.invalid_or_expired_invitation'));
        }

        return [
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
            ],
            'organization' => [
                'id' => $validOrganization->id,
                'name' => $validOrganization->name,
            ],
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Accept organization invitation with password setup.
     *
     * Flow:
     * 1. Validate token and find user
     * 2. Change password first
     * 3. Check user role
     * 4. Update organization status based on role
     * 5. Activate user status
     */
    public function acceptInvitationWithPassword(
        string $token,
        string $password
    ): array {
        return DB::transaction(function () use ($token, $password) {
            // Step 1: Get email from token
            $email = $this->getEmailFromToken($token);

            if (! $email) {
                throw new \RuntimeException(__('organization.invalid_or_expired_invitation'));
            }

            // Step 2: Find user
            $user = User::where('email', $email)->firstOrFail();

            // Step 3: Get all pending organization memberships for this user
            $organizationIds = \DB::table('organization_user')
                ->where('user_id', $user->id)
                ->where('status', OrganizationUserStatus::PENDING->value)
                ->pluck('organization_id');

            $organizations = Organization::whereIn('id', $organizationIds)->get();

            if ($organizations->isEmpty()) {
                throw new \RuntimeException(__('organization.no_pending_invitations'));
            }

            // Step 4: Validate token against organizations
            $validOrganization = null;
            foreach ($organizations as $org) {
                $cacheKey = "invitation:{$email}:{$org->id}";
                $cachedData = Cache::get($cacheKey);

                if ($cachedData && hash('sha256', $token) === $cachedData['token']) {
                    // Check expiration (parse string back to Carbon)
                    $expirationDate = Carbon::parse($cachedData['expires_at']);
                    if (now()->greaterThan($expirationDate)) {
                        throw new \RuntimeException(__('organization.invitation_expired'));
                    }

                    $validOrganization = $org;
                    Cache::forget($cacheKey); // Remove used token
                    break;
                }
            }

            if ($validOrganization === null) {
                throw new \RuntimeException(__('organization.invalid_or_expired_invitation'));
            }

            // Step 5: Change password FIRST (before any other updates)
            $user->update([
                'password' => Hash::make($password),
                'email_verified_at' => now(), // Auto-verify email on invitation acceptance
            ]);

            // Step 6: Check user role and update accordingly
            $userRole = $user->role->name;

            // Step 7: Update organization membership status based on role
            if (in_array($userRole, ['organization_admin', 'project_manager', 'member'], true)) {
                // Activate organization membership
                $validOrganization->members()->updateExistingPivot($user->id, [
                    'status' => OrganizationUserStatus::ACTIVE->value,
                    'accepted_at' => now(),
                ]);
            }

            // Step 8: Update user status to active
            $user->update([
                'status' => UserStatus::ACTIVE->value,
                'must_change_password' => false,
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
