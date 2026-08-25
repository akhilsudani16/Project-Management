<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Random comments for projects
        Comment::factory()->count(10)->forProject()->create([
            'commentable_id' => Project::inRandomOrder()->first()?->id,
        ]);

        // Random comments for tasks
        Comment::factory()->count(30)->forTask()->create([
            'commentable_id' => Task::inRandomOrder()->first()?->id,
        ]);
    }
}
