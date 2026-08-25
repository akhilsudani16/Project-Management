<?php

namespace Database\Seeders;

use App\Models\Attachment;
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
        // Random attachments for projects
        Attachment::factory()->count(5)->forProject()->create([
            'attachable_id' => Project::inRandomOrder()->first()?->id,
            ]);

        // Random attachments for tasks
        Attachment::factory()->count(15)->forTask()->create([
            'attachable_id' => Task::inRandomOrder()->first()?->id,
            ]);

        // Random attachments for users
        Attachment::factory()->count(10)->forUser()->create([
            'attachable_id' => User::inRandomOrder()->first()?->id,
            ]);
    }
}
