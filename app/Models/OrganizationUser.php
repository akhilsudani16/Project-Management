<?php

namespace App\Models;

use App\Enums\OrganizationUserStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['organization_id', 'user_id', 'invited_by', 'status', 'invited_at', 'accepted_at', 'deleted_by'])]
class OrganizationUser extends Pivot
{
    use HasUuids, SoftDeletes;

    protected $table = 'organization_user';

    protected $casts = [
        'status' => OrganizationUserStatus::class,
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'deleted_by',
    ];

    // Relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
