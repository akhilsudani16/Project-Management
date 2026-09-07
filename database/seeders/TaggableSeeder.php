<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaggableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = Tag::all();

        if ($tags->isEmpty()) {
            return;
        }

        // Attach tags to projects
        $projects = Project::all();
        foreach ($projects as $project) {
            // 70% of projects get 1-4 tags
            if (rand(1, 10) <= 7) {
                $projectTags = $tags->random(min(rand(1, 4), $tags->count()));

                foreach ($projectTags as $tag) {
                    // Check if already attached
                    if (! $project->tags()->where('tag_id', $tag->id)->exists()) {
                        $project->tags()->attach($tag->id, [
                            'id' => Str::uuid()->toString(),
                        ]);
                    }
                }
            }
        }

        // Attach tags to tasks
        $tasks = Task::all();
        foreach ($tasks as $task) {
            // 60% of tasks get 1-3 tags
            if (rand(1, 10) <= 6) {
                $taskTags = $tags->random(min(rand(1, 3), $tags->count()));

                foreach ($taskTags as $tag) {
                    // Check if already attached
                    if (! $task->tags()->where('tag_id', $tag->id)->exists()) {
                        $task->tags()->attach($tag->id, [
                            'id' => Str::uuid()->toString(),
                        ]);
                    }
                }
            }
        }
    }
}
