<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create projects with different statuses
        Project::factory()->count(15)->active()->create();
        Project::factory()->count(5)->draft()->create();
        Project::factory()->count(3)->onHold()->create();
        Project::factory()->count(4)->completed()->create();
        Project::factory()->count(2)->archived()->create();
        Project::factory()->count(1)->deleted()->create();
    }
}
