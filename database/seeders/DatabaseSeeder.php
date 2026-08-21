<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if running in production
        if (app()->environment('production')) {
            $this->command->error('Cannot run seeders in production!');

            return;
        }

        $this->command->info('Starting database seeding...');

        // Seed in dependency order
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
            CommentSeeder::class,
            TagSeeder::class,
            AttachmentSeeder::class,
            ActivityLogSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info(' Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('Test Credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'superadmin@example.com', 'password'],
                ['Organization Admin', 'alice@example.com', 'password'],
                ['Organization Admin', 'bob@example.com', 'password'],
                ['Project Manager', 'carol@example.com', 'password'],
                ['Project Manager', 'david@example.com', 'password'],
                ['Member', 'emma@example.com', 'password'],
                ['Member', 'frank@example.com', 'password'],
                ['Member', 'grace@example.com', 'password'],
                ['Member (Unverified)', 'henry@example.com', 'password'],
                ['Member (Must Change Password)', 'isabella@example.com', 'TempPassword123!'],
            ]
        );
    }
}
