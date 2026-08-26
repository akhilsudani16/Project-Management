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
     * Role permission mappings using Enum references
     */
    private static function getRolePermissions(): array
    {
        return [
            UserRole::SUPER_ADMIN->value => [
                'all' => true,
                'permissions' => [],
            ],

            UserRole::ORGANIZATION_ADMIN->value => [
                'all' => false,
                'permissions' => array_merge(
                    Permission::tagPermissions(),
                    Permission::attachmentPermissions(),
                    [
                        Permission::CREATE_PROJECT,
                        Permission::UPDATE_PROJECT,
                        Permission::VIEW_PROJECT,
                        Permission::DELETE_PROJECT,
                        Permission::ASSIGN_PROJECT_USER,
                        Permission::REMOVE_PROJECT_USER,

                        Permission::CREATE_TASK,
                        Permission::UPDATE_TASK,
                        Permission::VIEW_TASK,
                        Permission::DELETE_TASK,
                        Permission::ASSIGN_TASK,
                        Permission::VIEW_TEAM_TASK,

                        Permission::CREATE_COMMENT,
                        Permission::UPDATE_COMMENT,
                        Permission::DELETE_COMMENT,

                        Permission::VIEW_ACTIVITY_LOG,
                    ]
                ),
            ],

            UserRole::PROJECT_MANAGER->value => [
                'all' => false,
                'permissions' => array_merge(
                    Permission::attachmentPermissions(),
                    [
                        Permission::VIEW_PROJECT,
                        Permission::UPDATE_OWN_PROJECT,
                        Permission::ASSIGN_PROJECT_USER,

                        Permission::CREATE_TASK,
                        Permission::UPDATE_TASK,
                        Permission::VIEW_TASK,
                        Permission::DELETE_TASK,
                        Permission::ASSIGN_TASK,
                        Permission::VIEW_TEAM_TASK,

                        Permission::VIEW_USER,

                        Permission::CREATE_COMMENT,
                        Permission::UPDATE_OWN_COMMENT,
                        Permission::DELETE_OWN_COMMENT,

                        Permission::VIEW_TAG,
                        Permission::ATTACH_TAG,

                        Permission::VIEW_ACTIVITY_LOG,
                    ]
                ),
            ],

            UserRole::MEMBER->value => [
                'all' => false,
                'permissions' => array_merge(
                    Permission::attachmentPermissions(),
                    [
                        Permission::VIEW_OWN_PROJECT,

                        Permission::VIEW_OWN_TASK,
                        Permission::UPDATE_OWN_TASK,
                        Permission::VIEW_ASSIGNED_TASK,
                        Permission::UPDATE_ASSIGNED_TASK,
                        Permission::VIEW_TEAM_TASK,

                        Permission::VIEW_USER,

                        Permission::CREATE_COMMENT,
                        Permission::UPDATE_OWN_COMMENT,
                        Permission::DELETE_OWN_COMMENT,

                        Permission::VIEW_TAG,

                        Permission::VIEW_OWN_ACTIVITY_LOG,
                    ]
                ),
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure all Enum cases exist in the DB
        foreach (Permission::cases() as $permission) {
            PermissionModel::firstOrCreate(['name' => $permission->value]);
        }

        // 2. Load all permissions into memory once [name => id]
        $allPermissions = PermissionModel::pluck('id', 'name');

        // 3. Assign permissions to roles
        foreach (self::getRolePermissions() as $roleName => $config) {
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                continue;
            }

            if ($config['all'] === true) {
                $role->permissions()->sync($allPermissions->values());
            } else {
                // Convert Enum objects to string values safely
                $permissionValues = array_map(
                    fn (Permission $permission) => $permission->value,
                    $config['permissions']
                );

                // Map string values to IDs without database queries
                $permissionIds = $allPermissions->only(array_unique($permissionValues))->values();

                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
