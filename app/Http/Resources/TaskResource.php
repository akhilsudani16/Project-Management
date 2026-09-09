<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;

/**
 * @mixin Task
 */
class TaskResource extends BaseApiResource
{
    protected function transformData(Request $request): ?object
    {
        return (object) [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project' => $this->whenLoaded('project', fn () => (new ProjectResource($this->project))->transformData($request)),
            'assigned_to' => $this->whenLoaded('user', fn () => (new UserResource($this->user))->transformData($request)),
            'created_by' => $this->whenLoaded('creator', fn () => (new UserResource($this->creator))->transformData($request)),
            'comments' => $this->whenLoaded('comments', fn () => $this->comments->map(fn ($comment) => (new CommentResource($comment))->transformData($request))),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($tag) => (new TagResource($tag))->transformData($request))),
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($attachment) => (new AttachmentResource($attachment))->transformData($request))),
        ];
    }
}
