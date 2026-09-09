<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ApiResourceCollection extends BaseApiCollection
{
    protected ?string $resourceClass = null;

    public function setResourceClass(string $resourceClass): self
    {
        $this->collects = $resourceClass;

        return $this;
    }

    public function toArray(Request $request): array
    {
        // Transform each item to just the data portion (without status/message wrapper)
        $data = $this->collection->map(function ($resource) use ($request) {
            if ($resource instanceof BaseApiResource) {
                // Get the transformed data array from the resource
                $resourceArray = $resource->toArray($request);

                // Return only the 'data' portion, not the full wrapper
                return $resourceArray['data'] ?? $resourceArray;
            }

            return $resource;
        })->toArray();

        return [
            'status' => $this->status->value,
            'message' => $this->message,
            'data' => $data,
            'errors' => $this->errors ? (object) $this->errors : null,
            'meta' => $this->buildMeta(),
        ];
    }
}
