<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['permission_id', 'role_id'])]
class PermissionRole extends Pivot
{
    use HasUuids;

    protected $table = 'permission_role';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Relationships
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
