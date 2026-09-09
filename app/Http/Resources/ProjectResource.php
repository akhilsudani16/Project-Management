<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;

/**
 * @mixin Project
 */
class ProjectResource extends BaseApiResource
{
    protected function transformData(Request $request): ?object
    {
        return (object) [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'organization' => $this->whenLoaded('organization', fn () => (new OrganizationResource($this->organization))->transformData($request)),
            'created_by' => $this->whenLoaded('createdBy', fn () => (new UserResource($this->createdBy))->transformData($request)),
            'members_count' => $this->when(isset($this->members_count), $this->members_count),
            'tasks_count' => $this->when(isset($this->tasks_count), $this->tasks_count),
            'members' => $this->whenLoaded('members', fn () => $this->members->map(fn ($member) => (new UserResource($member))->transformData($request))),
        ];
    }
}
