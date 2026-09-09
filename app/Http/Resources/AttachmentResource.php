<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Attachment;
use Illuminate\Http\Request;

/**
 * @mixin Attachment
 */
class AttachmentResource extends BaseApiResource
{
    protected function transformData(Request $request): ?object
    {
        return (object) [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'attachable_type' => $this->attachable_type,
            'attachable_id' => $this->attachable_id,
            'user' => $this->whenLoaded('user', fn () => (new UserResource($this->user))->transformData($request)),
            'created_at' => $this->created_at,
        ];
    }
}
