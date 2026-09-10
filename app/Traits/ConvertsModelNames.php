<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

trait ConvertsModelNames
{
    /**
     * Convert short model name to fully qualified class name.
     */
    protected function getFullModelClass(?string $shortName): ?string
    {
        if (! $shortName) {
            return null;
        }

        return match ($shortName) {
            'User' => User::class,
            'Project' => Project::class,
            'Task' => Task::class,
            'Comment' => Comment::class,
            default => null,
        };
    }

    /**
     * Convert fully qualified class name to short model name.
     */
    protected function getShortModelName(?string $fullClassName): ?string
    {
        if (! $fullClassName) {
            return null;
        }

        return match ($fullClassName) {
            User::class, 'App\\Models\\User' => 'User',
            Project::class, 'App\\Models\\Project' => 'Project',
            Task::class, 'App\\Models\\Task' => 'Task',
            Comment::class, 'App\\Models\\Comment' => 'Comment',
            default => class_basename($fullClassName), // Fallback to class basename
        };
    }
}
