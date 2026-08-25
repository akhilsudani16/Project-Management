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
        // Super Admin
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);

        // Organization Admins
        User::factory()->organizationAdmin()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
        ]);

        User::factory()->organizationAdmin()->create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
        ]);

        // Project Managers
        User::factory()->projectManager()->create([
            'name' => 'Carol Davis',
            'email' => 'carol@example.com',
        ]);

        User::factory()->projectManager()->create([
            'name' => 'David Wilson',
            'email' => 'david@example.com',
        ]);

        // Members
        User::factory()->member()->create([
            'name' => 'Emma Brown',
            'email' => 'emma@example.com',
        ]);

        User::factory()->member()->create([
            'name' => 'Frank Miller',
            'email' => 'frank@example.com',
        ]);

        User::factory()->member()->create([
            'name' => 'Grace Lee',
            'email' => 'grace@example.com',
        ]);

        // Unverified user
        User::factory()->member()->unverified()->create([
            'name' => 'Henry Taylor',
            'email' => 'henry@example.com',
        ]);

        // User that must change password
        User::factory()->member()->create([
            'name' => 'Isabella Garcia',
            'email' => 'isabella@example.com',
            'password' => bcrypt('TempPassword123!'),
            'must_change_password' => true,
        ]);

        // Additional random users
        User::factory()->count(10)->create();
    }
}
