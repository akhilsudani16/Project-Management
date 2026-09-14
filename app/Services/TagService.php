<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\Project;
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
     * Create a new tag.
     * Optionally attach to a taggable resource (Project/Task) during creation.
     */
    public function create(array $data, User $creator): Tag
    {
        $tag = Tag::create([
            'organization_id' => $data['organization_id'],
            'name' => $data['name'],
        ]);

        // Optionally attach to resource during creation
        if (isset($data['taggable_type_full'], $data['taggable_id'])) {
            $taggableClass = $data['taggable_type_full'];
            $taggable = $taggableClass::find($data['taggable_id']);

            if ($taggable) {
                $taggable->tags()->attach($tag->id, ['id' => Str::uuid()]);
            }
        }

        return $tag;
    }

    /**
     * Update an existing tag.
     * Only updates fields that are present in the request data.
     */
    public function update(Tag $tag, array $data): Tag
    {
        // Only update fields that exist in the request
        $updateData = array_intersect_key($data, array_flip(['name']));

        $tag->update($updateData);

        return $tag->fresh();
    }

    /**
     * Delete a tag.
     */
    public function delete(Tag $tag): bool
    {
        // Detach from all projects
        $tag->projects()->detach();

        // Detach from all tasks
        $tag->tasks()->detach();

        return $tag->delete();
    }

    /**
     * Attach tag to task.
     */
    public function attachToTask(Tag $tag, Task $task): bool
    {
        if (! $task->tags()->where('tag.id', $tag->id)->exists()) {
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

    /**
     * Attach tag to project.
     */
    public function attachToProject(Tag $tag, $project): bool
    {
        if (! $project->tags()->where('tag.id', $tag->id)->exists()) {
            $project->tags()->attach($tag->id, ['id' => Str::uuid()]);

            return true;
        }

        return false;
    }

    /**
     * Detach tag from project.
     */
    public function detachFromProject(Tag $tag, $project): bool
    {
        return (bool) $project->tags()->detach($tag->id);
    }
}
