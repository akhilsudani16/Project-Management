<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Comment;
use App\Traits\ConvertsModelNames;
use Illuminate\Http\Request;

/**
 * @mixin Comment
 */
class CommentResource extends BaseApiResource
{
    use ConvertsModelNames;

    protected function transformData(Request $request): ?object
    {
        return (object) [
            'id' => $this->id,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->whenLoaded('user', fn () => (new UserResource($this->user))->getData($request)),
            'commentable_type' => $this->getShortModelName($this->commentable_type),
            'commentable_id' => $this->commentable_id,
        ];
    }
}
