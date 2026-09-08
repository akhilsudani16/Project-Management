<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'email_verified_at' => $this->resource->email_verified_at,
            'status' => $this->resource->status,
            'must_change_password' => $this->resource->must_change_password,
            'phone' => $this->resource->phone,
            'job_title' => $this->resource->job_title,
            'location' => $this->resource->location,
            'avatar_path' => $this->resource->avatar_path,
            'bio' => $this->resource->bio,
            'role' => [
                'id' => $this->resource->role?->id,
                'name' => $this->resource->role?->name,
                'description' => $this->resource->role?->description,
            ],
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
