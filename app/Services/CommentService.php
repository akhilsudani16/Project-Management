<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentService
{
    /**
     * List comments for a resource.
     *
     * @param  Project|Task  $commentable
     */
    public function list(
        Model $commentable,
        int $perPage = 15
    ): LengthAwarePaginator {
        /** @var Project|Task $commentable */
        return $commentable->comments()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get comment by ID.
     */
    public function getById(string $id): Comment
    {
        return Comment::with(['user', 'commentable'])->findOrFail($id);
    }

    /**
     * Create a new comment.
     *
     * @param  Project|Task  $commentable
     */
    public function create(Model $commentable, array $data, User $creator): Comment
    {
        /** @var Project|Task $commentable */
        return Comment::create([
            'commentable_type' => get_class($commentable),
            'commentable_id' => $commentable->id,
            'user_id' => $creator->id,
            'body' => $data['body'],
        ]);
    }

    /**
     * Update an existing comment.
     */
    public function update(Comment $comment, array $data): Comment
    {
        $comment->update([
            'body' => $data['body'],
        ]);

        return $comment->fresh();
    }

    /**
     * Delete a comment.
     */
    public function delete(Comment $comment, User $deletedBy): bool
    {
        $comment->deleted_by = $deletedBy->id;
        $comment->save();

        return $comment->delete();
    }

    /**
     * Get comments for a project.
     */
    public function getProjectComments(Project $project, int $perPage = 15): LengthAwarePaginator
    {
        return $this->list($project, $perPage);
    }

    /**
     * Get comments for a task.
     */
    public function getTaskComments(Task $task, int $perPage = 15): LengthAwarePaginator
    {
        return $this->list($task, $perPage);
    }
}
