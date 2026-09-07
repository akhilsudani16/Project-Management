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
            OrganizationUserSeeder::class,
            ProjectSeeder::class,
            ProjectUserSeeder::class,
            TaskSeeder::class,
            CommentSeeder::class,
            TagSeeder::class,
            TaggableSeeder::class,
            AttachmentSeeder::class,
            ActivityLogSeeder::class,
        ]);
        $this->command->info(' Database seeding completed successfully!');
    }
}
