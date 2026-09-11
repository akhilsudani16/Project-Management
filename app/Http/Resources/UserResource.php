<?php

namespace App\Http\Resources;

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
                'name' => $this->resource->name,
                'email' => $this->resource->email,
                'role' => $this->resource->role?->name,
            ];
        }

        // Full response (for profile, detailed views)
        return (object) [
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'job_title' => $this->resource->job_title,
            'location' => $this->resource->location,
            'avatar_path' => $this->resource->avatar_path,
            'bio' => $this->resource->bio,
            'role' => (object) [
                'name' => $this->resource->role?->name,
            ],
        ];
    }
}
