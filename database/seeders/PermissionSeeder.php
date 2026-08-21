<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions from the Permission enum
        foreach (Permission::cases() as $permission) {
            \App\Models\Permission::firstOrCreate(['name' => $permission->value]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        $superAdminRole = Role::where('name', UserRole::SUPER_ADMIN->value)->first();
        $orgAdminRole = Role::where('name', UserRole::ORGANIZATION_ADMIN->value)->first();
        $projectManagerRole = Role::where('name', UserRole::PROJECT_MANAGER->value)->first();
        $memberRole = Role::where('name', UserRole::MEMBER->value)->first();

        // Super Admin gets all permissions
        $allPermissions = \App\Models\Permission::all();
        foreach ($allPermissions as $permission) {
            $superAdminRole->givePermissionTo($permission);
        }

        // Organization Admin permissions
        $orgAdminPermissions = [
            'create_organization', 'update_organization', 'view_organization', 'delete_organization',
            'create_project', 'update_project', 'view_project', 'delete_project',
            'create_task', 'update_task', 'view_task', 'delete_task',
            'create_user', 'update_user', 'view_user', 'delete_user',
        ];
        foreach ($orgAdminPermissions as $permName) {
            $permission = \App\Models\Permission::where('name', $permName)->first();
            if ($permission) {
                $orgAdminRole->givePermissionTo($permission);
            }
        }

        // Project Manager permissions
        $pmPermissions = [
            'create_project', 'update_project', 'view_project',
            'create_task', 'update_task', 'view_task', 'delete_task',
            'view_user',
        ];
        foreach ($pmPermissions as $permName) {
            $permission = \App\Models\Permission::where('name', $permName)->first();
            if ($permission) {
                $projectManagerRole->givePermissionTo($permission);
            }
        }

        // Member permissions
        $memberPermissions = [
            'view_project', 'view_task', 'view_user',
        ];
        foreach ($memberPermissions as $permName) {
            $permission = \App\Models\Permission::where('name', $permName)->first();
            if ($permission) {
                $memberRole->givePermissionTo($permission);
            }
        }
    }
}
