<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine if the user can view any comments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the comment.
     */
    public function view(User $user, Comment $comment): bool
    {
        $commentable = $comment->commentable;

        // Check access based on commentable type
        if ($commentable instanceof Project) {
            return $user->hasAccessToProject($commentable);
        }

        if ($commentable instanceof Task) {
            $project = $commentable->project;
            if ($project instanceof Project) {
                return $user->hasAccessToProject($project);
            }
        }

        return false;
    }

    /**
     * Determine if the user can create comments.
     */
    public function create(User $user): bool
    {
        // Resource-specific access check will be done at controller level
        return true;
    }

    /**
     * Determine if the user can update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Users can update their own comments
        if ($comment->user_id === $user->id) {
            return true;
        }

        // Super Admins can moderate any comment
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Org Admins and PMs can moderate comments in their scope
        $commentable = $comment->commentable;

        if ($commentable instanceof Project) {
            return $user->canManageProject($commentable);
        }

        if ($commentable instanceof Task) {
            $project = $commentable->project;
            if ($project instanceof Project) {
                return $user->canManageProject($project);
            }
        }

        return false;
    }

    /**
     * Determine if the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Same rules as update
        return $this->update($user, $comment);
    }

    /**
     * Determine if the user can restore the comment.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return $this->delete($user, $comment);
    }
}
