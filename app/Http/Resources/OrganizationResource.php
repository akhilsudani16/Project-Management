<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Organization;
use Illuminate\Http\Request;

/**
 * @mixin Organization
 */
class OrganizationResource extends BaseApiResource
{
    protected function transformData(Request $request): ?object
    {
        return (object) [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->resource->status?->value,
            'created_by' => $this->whenLoaded('createdBy', fn () => (new UserResource($this->createdBy))->getData($request)),
            'members_count' => $this->when(isset($this->members_count), $this->members_count),
            'projects_count' => $this->when(isset($this->projects_count), $this->projects_count),
            'members' => $this->whenLoaded('members', fn () => $this->members->map(fn ($member) => (new UserResource($member))->getData($request))),
        ];
    }
}
