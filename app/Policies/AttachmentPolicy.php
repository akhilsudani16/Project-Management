<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    /**
     * Determine if the user can download the attachment.
     */
    public function download(User $user, Attachment $attachment): bool
    {
        // Super Admin can download anything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User uploaded it - can download
        if ($attachment->user_id === $user->id) {
            return true;
        }

        // Check if user can access the parent resource
        $attachable = $attachment->attachable;

        if (! $attachable) {
            return false;
        }

        return match ($attachment->attachable_type) {
            'App\\Models\\User' => $this->canAccessUserAttachment($user, $attachable),
            'App\\Models\\Project' => $user->can('view', $attachable),
            'App\\Models\\Task' => $user->can('view', $attachable),
            'App\\Models\\Comment' => $this->canAccessCommentAttachment($user, $attachable),
            default => false,
        };
    }

    /**
     * Determine if the user can delete the attachment.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        // Super Admin can delete anything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // User uploaded it - can delete their own
        if ($attachment->user_id === $user->id) {
            return true;
        }

        // Check if user can moderate the parent resource
        $attachable = $attachment->attachable;

        if (! $attachable) {
            return false;
        }

        return match ($attachment->attachable_type) {
            'App\\Models\\User' => false, // Can't delete others' profile attachments
            'App\\Models\\Project' => $user->can('update', $attachable),
            'App\\Models\\Task' => $user->can('update', $attachable),
            'App\\Models\\Comment' => $user->can('update', $attachable),
            default => false,
        };
    }

    private function canAccessUserAttachment(User $user, User $attachableUser): bool
    {
        // Users can only access their own profile attachments
        return $user->id === $attachableUser->id;
    }

    private function canAccessCommentAttachment(User $user, $comment): bool
    {
        // If user can view the comment's parent (task/project), they can view attachments
        $commentable = $comment->commentable;

        if (! $commentable) {
            return false;
        }

        return $user->can('view', $commentable);
    }
}
