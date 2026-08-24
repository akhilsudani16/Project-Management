<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['project_id', 'user_id', 'assigned_by', 'assigned_at', 'invitation_token', 'invited_by', 'invited_at', 'invitation_expires_at', 'accepted_at', 'deleted_by'])]
class ProjectUser extends Pivot
{
    use HasUuids, SoftDeletes;

    protected $table = 'project_user';

    protected $casts = [
        'assigned_at' => 'datetime',
        'invited_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
