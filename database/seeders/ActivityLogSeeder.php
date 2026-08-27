<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create random activity logs
        ActivityLog::factory()->count(10)->forProject()->create([
            'targetable_id' => Project::inRandomOrder()->first()?->getKey(),
        ]);

        ActivityLog::factory()->count(10)->forTask()->create([
            'targetable_id' => Task::inRandomOrder()->first()?->getKey(),
        ]);

        ActivityLog::factory()->count(10)->forUser()->create([
            'targetable_id' => User::inRandomOrder()->first()?->getKey(),
        ]);

        ActivityLog::factory()->count(10)->forOrganization()->create([
            'targetable_id' => Organization::inRandomOrder()->first()?->getKey(),
        ]);
    }
}
