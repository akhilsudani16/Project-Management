<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->superAdmin()->count(1)->create();
        User::factory()->organizationAdmin()->count(4)->create();
        User::factory()->projectManager()->count(5)->create();
        User::factory()->member()->count(9)->create();
        User::factory()->member()->unverified()->count(1)->create();
        User::factory()->member()->deleted()->count(1)->create();
        User::factory()->member()->mustChangePassword()->count(1)->create();
    }
}
