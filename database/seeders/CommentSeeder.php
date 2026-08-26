<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Database\Factories\CommentFactory;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Random comments for projects
        CommentFactory::new()->count(10)->forProject()->create([
            'commentable_id' => Project::inRandomOrder()->first()?->getKey(),
        ]);

        // Random comments for tasks
        CommentFactory::new()->count(30)->forTask()->create([
            'commentable_id' => Task::inRandomOrder()->first()?->getKey(),
        ]);
    }
}
