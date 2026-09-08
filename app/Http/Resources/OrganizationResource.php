<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Organization
 *
 * @property-read string|null $description
 */
class OrganizationResource extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'members_count' => $this->when(isset($this->members_count), $this->members_count),
            'projects_count' => $this->when(isset($this->projects_count), $this->projects_count),
            'members' => UserResource::collection($this->whenLoaded('members')),
        ];
    }
}
