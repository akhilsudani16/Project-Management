<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * SEEDED CREDENTIALS (local/development only):
     * All users have password: "password"
     * User with temporary password has: "TempPassword123!" (must change on first login)
     *
     * Users created:
     * - 1 Super Admin (email will be randomly generated)
     * - 4 Organization Admins
     * - 5 Project Managers
     * - 9 Regular Members
     * - 1 Unverified Member (pending email verification)
     * - 1 Deleted Member (soft deleted)
     * - 1 Member with temporary password (must_change_password = true)
     * - 1 Member with failed login attempts (1-2 attempts)
     * - 1 Locked Out Member (3 attempts, locked for 15 minutes)
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
        User::factory()->member()->withFailedLogins()->count(1)->create();
        User::factory()->member()->lockedOut()->count(1)->create();
    }
}
