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
        // Create random tasks
        Task::factory()->count(50)->create();
        Task::factory()->count(1)->deleted()->create();
        Task::factory()->count(49)->create();
    }
}
