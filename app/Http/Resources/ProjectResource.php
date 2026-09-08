<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'members_count' => $this->when(isset($this->members_count), $this->members_count),
            'tasks_count' => $this->when(isset($this->tasks_count), $this->tasks_count),
            'members' => UserResource::collection($this->whenLoaded('members')),
        ];
    }
}
