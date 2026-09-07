<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create regular tasks
        Task::factory()->count(50)->create();

        // Create unassigned tasks
        Task::factory()->count(5)->unassigned()->create();

        // Create deleted task
        Task::factory()->count(1)->deleted()->create();
    }
}
