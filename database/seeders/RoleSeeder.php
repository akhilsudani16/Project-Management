<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::factory()->superAdmin()->create();
        Role::factory()->organizationAdmin()->create();
        Role::factory()->projectManager()->create();
        Role::factory()->member()->create();
    }
}
