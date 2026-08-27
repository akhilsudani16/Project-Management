<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create every permission defined in the enum.
        foreach (Permission::cases() as $permission) {
            PermissionModel::firstOrCreate(['name' => $permission->value]);
        }

        $permissionIds = PermissionModel::pluck('id', 'name');

        $rolePermissions = [
            UserRole::ORGANIZATION_ADMIN->value => Permission::organizationAdminPermissions(),
            UserRole::PROJECT_MANAGER->value => Permission::projectManagerPermissions(),
            UserRole::MEMBER->value => Permission::memberPermissions(),
        ];

        // 3. Sync each role to its permissions.
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                continue;
            }

            $ids = $permissionIds->only(array_column($permissions, 'value'))->values();

            $role->permissions()->sync($ids);
        }
    }
}
