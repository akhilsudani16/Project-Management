<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create organizations with different statuses
        Organization::factory()->count(10)->active()->create();
        Organization::factory()->count(3)->inactive()->create();
        Organization::factory()->count(2)->archived()->create();
    }
}
