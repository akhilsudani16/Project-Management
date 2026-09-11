<?php

namespace App\Http\Resources;

use App\Models\Attachment;
use App\Traits\ConvertsModelNames;
use Illuminate\Http\Request;

/**
 * @mixin Attachment
 */
class AttachmentResource extends BaseApiResource
{
    use ConvertsModelNames;

    protected function transformData(Request $request): ?object
    {
        // Extract filename from path for display
        $fileName = basename($this->resource->path);

        return (object) [
            'id' => $this->resource->id,
            'file_name' => $fileName,
            'path' => $this->resource->path,
            'attachable_type' => $this->getShortModelName($this->resource->attachable_type),
            'attachable_id' => $this->resource->attachable_id,
            'user' => $this->when($this->relationLoaded('user'), function () {
                return (object) [
                    'id' => $this->resource->user?->id,
                    'name' => $this->resource->user?->name,
                ];
            }),
            'download_url' => route('attachments.download', ['attachment' => $this->resource->id]),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
