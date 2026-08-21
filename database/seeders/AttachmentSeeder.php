<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder
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

        $emma = User::where('email', 'emma@example.com')->first();
        $frank = User::where('email', 'frank@example.com')->first();
        $grace = User::where('email', 'grace@example.com')->first();

        // Attachments for Project 1
        if ($project1) {
            Attachment::create([
                'attachable_type' => Project::class,
                'attachable_id' => $project1->id,
                'user_id' => $emma->id,
                'path' => 'attachments/projects/ecommerce-requirements.pdf',
            ]);

            Attachment::create([
                'attachable_type' => Project::class,
                'attachable_id' => $project1->id,
                'user_id' => $frank->id,
                'path' => 'attachments/projects/ecommerce-wireframes.fig',
            ]);
        }

        // Attachments for Project 3
        if ($project3) {
            Attachment::create([
                'attachable_type' => Project::class,
                'attachable_id' => $project3->id,
                'user_id' => $grace->id,
                'path' => 'attachments/projects/analytics-dashboard-mockup.png',
            ]);
        }

        // Attachments for Task 1
        if ($task1) {
            Attachment::create([
                'attachable_type' => Task::class,
                'attachable_id' => $task1->id,
                'user_id' => $emma->id,
                'path' => 'attachments/tasks/api-documentation.md',
            ]);

            Attachment::create([
                'attachable_type' => Task::class,
                'attachable_id' => $task1->id,
                'user_id' => $emma->id,
                'path' => 'attachments/tasks/api-postman-collection.json',
            ]);
        }

        // Attachments for Task 2
        if ($task2) {
            Attachment::create([
                'attachable_type' => Task::class,
                'attachable_id' => $task2->id,
                'user_id' => $frank->id,
                'path' => 'attachments/tasks/homepage-design-v1.png',
            ]);

            Attachment::create([
                'attachable_type' => Task::class,
                'attachable_id' => $task2->id,
                'user_id' => $frank->id,
                'path' => 'attachments/tasks/product-listing-design.png',
            ]);
        }

        // Attachments for user avatars
        Attachment::create([
            'attachable_type' => User::class,
            'attachable_id' => $emma->id,
            'user_id' => $emma->id,
            'path' => 'attachments/users/emma-profile-pic.jpg',
        ]);

        Attachment::create([
            'attachable_type' => User::class,
            'attachable_id' => $frank->id,
            'user_id' => $frank->id,
            'path' => 'attachments/users/frank-profile-pic.jpg',
        ]);

        // Attachments for comments
        $comment = Comment::where('body', 'LIKE', '%Figma link%')->first();
        if ($comment) {
            Attachment::create([
                'attachable_type' => Comment::class,
                'attachable_id' => $comment->id,
                'user_id' => $frank->id,
                'path' => 'attachments/comments/design-assets.zip',
            ]);
        }
    }
}
