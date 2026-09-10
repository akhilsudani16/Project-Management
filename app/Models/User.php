<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'role_id', 'password', 'must_change_password', 'status', 'bio', 'phone', 'job_title', 'location', 'avatar_path', 'failed_login_attempts', 'lockout_until', 'created_by', 'assigned_by', 'deleted_by'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'lockout_until' => 'datetime',
            'failed_login_attempts' => 'integer',
            'status' => UserStatus::class,
        ];
    }

    // Relationships
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->using(OrganizationUser::class)
            ->withPivot(['id', 'status', 'invited_by', 'invited_at', 'accepted_at'])
            ->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->using(ProjectUser::class)
            ->withPivot(['id', 'assigned_by', 'assigned_at', 'invitation_token', 'invited_by', 'invited_at', 'accepted_at'])
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification with signed URL.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    // Helper methods for authorization
    public function isSuperAdmin(): bool
    {
        /** @var Role|null $role */
        $role = $this->role;

        return $role?->name === 'super_admin';
    }

    public function isOrgAdmin(): bool
    {
        /** @var Role|null $role */
        $role = $this->role;

        return $role?->name === 'organization_admin';
    }

    public function isProjectManager(): bool
    {
        /** @var Role|null $role */
        $role = $this->role;

        return $role?->name === 'project_manager';
    }

    public function isMember(): bool
    {
        /** @var Role|null $role */
        $role = $this->role;

        return $role?->name === 'member';
    }

    public function hasAccessToOrganization(Organization $organization): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Organization Admins, PMs, and Members need to be explicitly assigned
        return $this->organizations()
            ->where('organizations.id', $organization->id)
            ->wherePivot('status', 'active')
            ->exists();
    }

    public function hasAccessToProject(Project $project): bool
    {
        // Super Admins have access to all projects
        if ($this->isSuperAdmin()) {
            return true;
        }

        /** @var Organization|null $organization */
        $organization = $project->organization;

        // Org Admins have access to all projects in their organizations
        if ($this->isOrgAdmin() && $organization !== null && $this->hasAccessToOrganization($organization)) {
            return true;
        }

        // PMs and Members need to be explicitly assigned to the project
        return $this->projects()
            ->where('projects.id', $project->id)
            ->exists();
    }

    public function canManageOrganization(Organization $organization): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isOrgAdmin() && $this->hasAccessToOrganization($organization)) {
            return true;
        }

        return false;
    }

    public function canManageProject(Project $project): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        /** @var Organization|null $organization */
        $organization = $project->organization;

        if ($this->isOrgAdmin() && $organization !== null && $this->hasAccessToOrganization($organization)) {
            return true;
        }

        if ($this->isProjectManager() && $this->hasAccessToProject($project)) {
            return true;
        }

        return false;
    }
}
