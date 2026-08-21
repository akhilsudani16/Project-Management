<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project1 = Project::where('name', 'E-Commerce Platform')->first();
        $project3 = Project::where('name', 'Data Analytics Dashboard')->first();

        $task1 = Task::where('title', 'Build product catalog API endpoints')->first();
        $task2 = Task::where('title', 'Design homepage and product listing pages')->first();
        $task3 = Task::where('title', 'Implement offline data synchronization')->first();

        $carol = User::where('email', 'carol@example.com')->first();
        $david = User::where('email', 'david@example.com')->first();
        $emma = User::where('email', 'emma@example.com')->first();
        $frank = User::where('email', 'frank@example.com')->first();
        $grace = User::where('email', 'grace@example.com')->first();

        // Comments on Project 1
        Comment::create([
            'commentable_type' => Project::class,
            'commentable_id' => $project1->id,
            'user_id' => $carol->id,
            'body' => 'Great progress on this project! Let\'s keep up the momentum and aim for the Q2 launch.',
        ]);

        Comment::create([
            'commentable_type' => Project::class,
            'commentable_id' => $project1->id,
            'user_id' => $emma->id,
            'body' => 'The API structure is looking solid. I\'ve completed the authentication module and moving on to the product endpoints.',
        ]);

        // Comments on Project 3
        Comment::create([
            'commentable_type' => Project::class,
            'commentable_id' => $project3->id,
            'user_id' => $david->id,
            'body' => 'We need to prioritize performance optimization. The dashboard is slow with large datasets.',
        ]);

        // Comments on Task 1
        if ($task1) {
            Comment::create([
                'commentable_type' => Task::class,
                'commentable_id' => $task1->id,
                'user_id' => $emma->id,
                'body' => 'I\'m working on the product listing endpoint. Should have it ready by EOD tomorrow.',
            ]);

            Comment::create([
                'commentable_type' => Task::class,
                'commentable_id' => $task1->id,
                'user_id' => $carol->id,
                'body' => 'Thanks Emma! Don\'t forget to add pagination and filtering options.',
            ]);

            Comment::create([
                'commentable_type' => Task::class,
                'commentable_id' => $task1->id,
                'user_id' => $emma->id,
                'body' => 'Will do! I\'m also adding search functionality by product name and SKU.',
            ]);
        }

        // Comments on Task 2
        if ($task2) {
            Comment::create([
                'commentable_type' => Task::class,
                'commentable_id' => $task2->id,
                'user_id' => $frank->id,
                'body' => 'I\'ve completed the initial mockups. Can we schedule a review session?',
            ]);

            Comment::create([
                'commentable_type' => Task::class,
                'commentable_id' => $task2->id,
                'user_id' => $carol->id,
                'body' => 'Sure! Let\'s meet tomorrow at 2 PM. Please share the Figma link before the meeting.',
            ]);
        }

        // Comments on Task 3
        if ($task3) {
            Comment::create([
                'commentable_type' => Task::class,
                'commentable_id' => $task3->id,
                'user_id' => $emma->id,
                'body' => 'The offline sync is working but I\'m running into issues with conflict resolution. Need to discuss the strategy.',
            ]);

            Comment::create([
                'commentable_type' => Task::class,
                'commentable_id' => $task3->id,
                'user_id' => $carol->id,
                'body' => 'Let\'s use last-write-wins for now. We can implement more sophisticated conflict resolution later if needed.',
            ]);
        }
    }
}
