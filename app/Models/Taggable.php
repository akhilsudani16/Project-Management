<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['tag_id', 'taggable_type', 'taggable_id'])]
class Taggable extends Pivot
{
    use HasUuids;

    protected $table = 'taggables';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Relationships
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
}
