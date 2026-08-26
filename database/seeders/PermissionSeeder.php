<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Permission groups by resource type
     */
    private const array PERMISSION_GROUPS = [
        'organization' => ['create_organization', 'update_organization', 'view_organization', 'delete_organization'],
        'project' => ['create_project', 'update_project', 'view_project', 'delete_project', 'assign_project_user', 'remove_project_user', 'view_own_project', 'update_own_project'],
        'task' => ['create_task', 'update_task', 'view_task', 'delete_task', 'assign_task', 'view_team_task', 'view_own_task', 'update_own_task', 'view_assigned_task', 'update_assigned_task'],
        'user' => ['create_user', 'update_user', 'view_user', 'delete_user'],
        'comment' => ['create_comment', 'update_comment', 'delete_comment', 'update_own_comment', 'delete_own_comment'],
        'tag' => ['create_tag', 'update_tag', 'delete_tag', 'view_tag', 'attach_tag'],
        'attachment' => ['upload_attachment', 'delete_attachment', 'view_attachment', 'delete_own_attachment'],
        'activity_log' => ['view_activity_log', 'view_own_activity_log'],
    ];

    /**
     * Role permission mappings using groups and specific permissions
     */
    private const array ROLE_PERMISSIONS = [
        UserRole::SUPER_ADMIN->value => '*',

        UserRole::ORGANIZATION_ADMIN->value => [
            'permissions' => [
                'create_project', 'update_project', 'view_project', 'delete_project', 'assign_project_user', 'remove_project_user',
                'create_task', 'update_task', 'view_task', 'delete_task', 'assign_task', 'view_team_task',
                'create_comment', 'update_comment', 'delete_comment',
                'create_tag', 'update_tag', 'delete_tag', 'view_tag', 'attach_tag',
                'upload_attachment', 'delete_attachment', 'view_attachment',
                'view_activity_log',
            ],
        ],

        UserRole::PROJECT_MANAGER->value => [
            'permissions' => [
                'view_project', 'update_own_project', 'assign_project_user',
                'create_task', 'update_task', 'view_task', 'delete_task', 'assign_task', 'view_team_task',
                'view_user',
                'create_comment', 'update_own_comment', 'delete_own_comment',
                'view_tag', 'attach_tag',
                'upload_attachment', 'delete_own_attachment', 'view_attachment',
                'view_activity_log',
            ],
        ],

        UserRole::MEMBER->value => [
            'permissions' => [
                'view_own_project',
                'view_own_task', 'update_own_task', 'view_assigned_task', 'update_assigned_task', 'view_team_task',
                'view_user',
                'create_comment', 'update_own_comment', 'delete_own_comment',
                'view_tag',
                'upload_attachment', 'delete_own_attachment', 'view_attachment',
                'view_own_activity_log',
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create all permissions from enum
        foreach (Permission::cases() as $permission) {
            \App\Models\Permission::firstOrCreate(['name' => $permission->value]);
        }

        // Assign permissions to roles
        foreach (self::ROLE_PERMISSIONS as $roleName => $config) {
            $role = Role::where('name', $roleName)->first();

            if ($config === '*') {
                // Super Admin gets all permissions
                $role->permissions()->sync(\App\Models\Permission::all()->pluck('id'));
            } else {
                $permissions = [];

                // Add permissions from groups
                if (isset($config['groups'])) {
                    foreach ($config['groups'] as $group) {
                        $permissions = array_merge($permissions, self::PERMISSION_GROUPS[$group]);
                    }
                }

                // Add specific permissions
                if (isset($config['permissions'])) {
                    $permissions = array_merge($permissions, $config['permissions']);
                }

                // Sync permissions to role
                $role->permissions()->sync(
                    \App\Models\Permission::whereIn('name', array_unique($permissions))->pluck('id')
                );
            }
        }
    }
}
