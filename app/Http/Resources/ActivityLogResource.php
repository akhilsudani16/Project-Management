<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

/**
 * @mixin ActivityLog
 */
class ActivityLogResource extends BaseApiResource
{
    protected function transformData(Request $request): ?object
    {
        return (object) [
            'id' => $this->id,
            'action' => $this->action,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'targetable_type' => $this->targetable_type,
            'targetable_id' => $this->targetable_id,
            'user' => $this->whenLoaded('user', fn () => (new UserResource($this->user))->transformData($request)),
            'created_at' => $this->created_at,
        ];
    }
}
