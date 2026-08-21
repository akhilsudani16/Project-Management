<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => UserRole::SUPER_ADMIN->value,
                'description' => 'Super Administrator with full system access',
            ],
            [
                'name' => UserRole::ORGANIZATION_ADMIN->value,
                'description' => 'Organization Administrator with access to assigned organizations',
            ],
            [
                'name' => UserRole::PROJECT_MANAGER->value,
                'description' => 'Project Manager with access to assigned projects',
            ],
            [
                'name' => UserRole::MEMBER->value,
                'description' => 'Team Member with limited access',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
