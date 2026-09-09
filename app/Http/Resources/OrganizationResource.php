<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Organization;
use Illuminate\Http\Request;

/**
 * @mixin Organization
 *
 * @property-read string|null $description
 */
class OrganizationResource extends BaseApiResource
{
    protected function transformData(Request $request): ?object
    {
        return (object) [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->whenLoaded('createdBy', fn () => (new UserResource($this->createdBy))->transformData($request)),
            'members_count' => $this->when(isset($this->members_count), $this->members_count),
            'projects_count' => $this->when(isset($this->projects_count), $this->projects_count),
            'members' => $this->whenLoaded('members', fn () => $this->members->map(fn ($member) => (new UserResource($member))->transformData($request))),
        ];
    }
}
