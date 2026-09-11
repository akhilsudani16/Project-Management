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
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->resource->status?->value,
            'created_by' => $this->whenLoaded('createdBy', function () {
                if ($this->createdBy === null) {
                    return null;
                }

                return [
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
        ];

        // Only add members if the relationship is loaded
        if ($this->relationLoaded('members')) {
            $data['members'] = $this->members->isEmpty()
                ? null
                : $this->members->map(fn ($member) => (new UserResource($member))->getData($request))->values()->toArray();
        }

        return (object) $data;
    }
}
