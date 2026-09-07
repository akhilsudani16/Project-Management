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
        // Create random activity logs for projects
        ActivityLog::factory()->count(15)->forProject()->create([
            'targetable_id' => fn () => Project::inRandomOrder()->first()?->getKey(),
        ]);

        // Create random activity logs for tasks
        ActivityLog::factory()->count(20)->forTask()->create([
            'targetable_id' => fn () => Task::inRandomOrder()->first()?->getKey(),
        ]);

        // Create random activity logs for users (login, logout, password changes)
        ActivityLog::factory()->count(25)->forUser()->create([
            'targetable_id' => fn () => User::inRandomOrder()->first()?->getKey(),
        ]);

        // Create random activity logs for organizations
        ActivityLog::factory()->count(10)->forOrganization()->create([
            'targetable_id' => fn () => Organization::inRandomOrder()->first()?->getKey(),
        ]);
    }
}
