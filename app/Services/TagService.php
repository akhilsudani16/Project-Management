<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TagService
{
    /**
     * List tags in an organization.
     */
    public function list(
        Organization $organization,
        int $perPage = 50
    ): LengthAwarePaginator {
        return $organization->tags()
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get tag by ID.
     */
    public function getById(string $id): Tag
    {
        return Tag::with(['organization'])->findOrFail($id);
    }

    /**
     * Create a new tag.
     */
    public function create(array $data, User $creator): Tag
    {
        return Tag::create([
            'organization_id' => $data['organization_id'],
            'name' => $data['name'],
            'color' => $data['color'] ?? '#3B82F6',
        ]);
    }

    /**
     * Update an existing tag.
     */
    public function update(Tag $tag, array $data): Tag
    {
        $tag->update(array_filter([
            'name' => $data['name'] ?? null,
            'color' => $data['color'] ?? null,
        ], fn ($value) => $value !== null));

        return $tag->fresh();
    }

    /**
     * Delete a tag.
     */
    public function delete(Tag $tag): bool
    {
        // Detach from all tasks
        $tag->tasks()->detach();

        return $tag->delete();
    }

    /**
     * Attach tag to task.
     */
    public function attachToTask(Tag $tag, Task $task): bool
    {
        if (! $task->tags()->where('tags.id', $tag->id)->exists()) {
            $task->tags()->attach($tag->id, ['id' => Str::uuid()]);

            return true;
        }

        return false;
    }

    /**
     * Detach tag from task.
     */
    public function detachFromTask(Tag $tag, Task $task): bool
    {
        return (bool) $task->tags()->detach($tag->id);
    }
}
