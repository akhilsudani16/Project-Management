<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $superAdminRole = Role::where('name', UserRole::SUPER_ADMIN->value)->first();
        $orgAdminRole = Role::where('name', UserRole::ORGANIZATION_ADMIN->value)->first();
        $projectManagerRole = Role::where('name', UserRole::PROJECT_MANAGER->value)->first();
        $memberRole = Role::where('name', UserRole::MEMBER->value)->first();

        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $superAdminRole->id,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'phone' => '+1-555-0001',
            'job_title' => 'System Administrator',
            'location' => 'New York, USA',
            'avatar_path' => 'avatars/superadmin.png',
            'bio' => 'Platform administrator with full system access.',
        ]);

        // Create Organization Admins
        $orgAdmin1 = User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'role_id' => $orgAdminRole->id,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'phone' => '+1-555-0002',
            'job_title' => 'Organization Administrator',
            'location' => 'San Francisco, USA',
            'avatar_path' => 'avatars/alice.png',
            'bio' => 'Managing organization operations and team members.',
            'created_by' => $superAdmin->id,
        ]);

        $orgAdmin2 = User::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'role_id' => $orgAdminRole->id,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'phone' => '+1-555-0003',
            'job_title' => 'Organization Lead',
            'location' => 'London, UK',
            'avatar_path' => 'avatars/bob.png',
            'bio' => 'Leading organizational strategy and growth.',
            'created_by' => $superAdmin->id,
        ]);

        // Create Project Managers
        $pm1 = User::create([
            'name' => 'Carol Davis',
            'email' => 'carol@example.com',
            'password' => Hash::make('password'),
            'role_id' => $projectManagerRole->id,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'phone' => '+1-555-0004',
            'job_title' => 'Project Manager',
            'location' => 'Austin, USA',
            'avatar_path' => 'avatars/carol.png',
            'bio' => 'Delivering projects on time and within budget.',
            'created_by' => $orgAdmin1->id,
        ]);

        $pm2 = User::create([
            'name' => 'David Wilson',
            'email' => 'david@example.com',
            'password' => Hash::make('password'),
            'role_id' => $projectManagerRole->id,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'phone' => '+1-555-0005',
            'job_title' => 'Senior Project Manager',
            'location' => 'Toronto, Canada',
            'avatar_path' => 'avatars/david.png',
            'bio' => 'Expert in agile project management methodologies.',
            'created_by' => $orgAdmin2->id,
        ]);

        // Create Members
        $member1 = User::create([
            'name' => 'Emma Brown',
            'email' => 'emma@example.com',
            'password' => Hash::make('password'),
            'role_id' => $memberRole->id,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'phone' => '+1-555-0006',
            'job_title' => 'Software Developer',
            'location' => 'Seattle, USA',
            'avatar_path' => 'avatars/emma.png',
            'bio' => 'Full-stack developer specializing in Laravel and Vue.js.',
            'created_by' => $pm1->id,
        ]);

        $member2 = User::create([
            'name' => 'Frank Miller',
            'email' => 'frank@example.com',
            'password' => Hash::make('password'),
            'role_id' => $memberRole->id,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'phone' => '+1-555-0007',
            'job_title' => 'UI/UX Designer',
            'location' => 'Los Angeles, USA',
            'avatar_path' => 'avatars/frank.png',
            'bio' => 'Creating beautiful and intuitive user experiences.',
            'created_by' => $pm1->id,
        ]);

        $member3 = User::create([
            'name' => 'Grace Lee',
            'email' => 'grace@example.com',
            'password' => Hash::make('password'),
            'role_id' => $memberRole->id,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE->value,
            'phone' => '+1-555-0008',
            'job_title' => 'QA Engineer',
            'location' => 'Boston, USA',
            'avatar_path' => 'avatars/grace.png',
            'bio' => 'Ensuring quality through comprehensive testing strategies.',
            'created_by' => $pm2->id,
        ]);

        // Create unverified user
        User::create([
            'name' => 'Henry Taylor',
            'email' => 'henry@example.com',
            'password' => Hash::make('password'),
            'role_id' => $memberRole->id,
            'email_verified_at' => null, // Unverified
            'status' => UserStatus::PENDING->value,
            'phone' => '+1-555-0009',
            'job_title' => 'Junior Developer',
            'location' => 'Chicago, USA',
            'avatar_path' => 'avatars/henry.png',
            'bio' => 'Eager to learn and contribute to the team.',
            'created_by' => $pm1->id,
        ]);

        // Create user that must change password
        User::create([
            'name' => 'Isabella Garcia',
            'email' => 'isabella@example.com',
            'password' => Hash::make('TempPassword123!'),
            'role_id' => $memberRole->id,
            'email_verified_at' => now(),
            'must_change_password' => true,
            'status' => UserStatus::ACTIVE->value,
            'phone' => '+1-555-0010',
            'job_title' => 'Business Analyst',
            'location' => 'Miami, USA',
            'avatar_path' => 'avatars/isabella.png',
            'bio' => 'Translating business requirements into technical solutions.',
            'created_by' => $orgAdmin1->id,
        ]);
    }
}
