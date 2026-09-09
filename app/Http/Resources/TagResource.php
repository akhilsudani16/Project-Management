<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Tag;
use Illuminate\Http\Request;

/**
 * @mixin Tag
 */
class TagResource extends BaseApiResource
{
    protected function transformData(Request $request): ?object
    {
        return (object) [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'organization' => $this->whenLoaded('organization', fn () => (new OrganizationResource($this->organization))->transformData($request)),
            'created_at' => $this->created_at,
        ];
    }
}
