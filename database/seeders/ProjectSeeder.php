<?php

namespace Database\Seeders;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create projects with different statuses using explicit factory class instantiation
        ProjectFactory::new()->count(15)->active()->create();
        ProjectFactory::new()->count(5)->draft()->create();
        ProjectFactory::new()->count(3)->onHold()->create();
        ProjectFactory::new()->count(4)->completed()->create();
        ProjectFactory::new()->count(2)->archived()->create();
        ProjectFactory::new()->count(1)->deleted()->create();
    }
}
