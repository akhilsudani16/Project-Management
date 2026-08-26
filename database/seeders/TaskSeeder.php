<?php

namespace Database\Seeders;

use App\Models\Task;
use Database\Factories\TaskFactory;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create random tasks using explicit factory class instantiation
        TaskFactory::new()->count(50)->create();
        TaskFactory::new()->count(1)->deleted()->create();
        TaskFactory::new()->count(49)->create();
    }
}
