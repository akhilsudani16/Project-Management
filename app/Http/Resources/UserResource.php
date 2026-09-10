<?php

namespace App\Http\Resources;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @mixin User
 */
class UserResource extends BaseApiResource
{
    private bool $minimal = false;

    /**
     * Set minimal mode (for login responses)
     */
    public function minimal(): self
    {
        $this->minimal = true;

        return $this;
    }

    protected function transformData(Request $request): ?object
    {
        // Minimal response (for login, quick lists)
        if ($this->minimal) {
            return (object) [
                'id' => $this->resource->id,
                'name' => $this->resource->name,
                'email' => $this->resource->email,
                'role' => $this->resource->role?->name,
                'created_at' => $this->resource->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->resource->updated_at?->format('Y-m-d H:i:s'),
            ];
        }

        // Full response (for profile, detailed views)
        return (object) [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            // 'email_verified_at' => $this->resource->email_verified_at,
            // 'status' => $this->resource->status instanceof UserStatus ? $this->resource->status->value : $this->resource->status,
            // 'must_change_password' => $this->resource->must_change_password,
            'phone' => $this->resource->phone,
            'job_title' => $this->resource->job_title,
            'location' => $this->resource->location,
            'avatar_path' => $this->resource->avatar_path,
            'bio' => $this->resource->bio,
            'role' => (object) [
                'id' => $this->resource->role?->id,
                'name' => $this->resource->role?->name,
                'description' => $this->resource->role?->description,
            ],
            // 'created_at' => $this->resource->created_at,
            // 'updated_at' => $this->resource->updated_at,
        ];
    }
}
