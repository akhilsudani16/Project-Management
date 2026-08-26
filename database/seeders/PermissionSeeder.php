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
        UserRole::SUPER_ADMIN->value => [
            'all' => true,
            'groups' => ['organization', 'project', 'task', 'user', 'comment', 'tag', 'attachment', 'activity_log'],
            'permissions' => [],
        ],

        UserRole::ORGANIZATION_ADMIN->value => [
            'all' => false,
            'groups' => ['tag', 'attachment'],
            'permissions' => [
                'create_project', 'update_project', 'view_project', 'delete_project', 'assign_project_user', 'remove_project_user',
                'create_task', 'update_task', 'view_task', 'delete_task', 'assign_task', 'view_team_task',
                'create_comment', 'update_comment', 'delete_comment',
                'view_activity_log',
            ],
        ],

        UserRole::PROJECT_MANAGER->value => [
            'all' => false,
            'groups' => ['attachment'],
            'permissions' => [
                'view_project', 'update_own_project', 'assign_project_user',
                'create_task', 'update_task', 'view_task', 'delete_task', 'assign_task', 'view_team_task',
                'view_user',
                'create_comment', 'update_own_comment', 'delete_own_comment',
                'view_tag', 'attach_tag',
                'view_activity_log',
            ],
        ],

        UserRole::MEMBER->value => [
            'all' => false,
            'groups' => ['attachment'],
            'permissions' => [
                'view_own_project',
                'view_own_task', 'update_own_task', 'view_assigned_task', 'update_assigned_task', 'view_team_task',
                'view_user',
                'create_comment', 'update_own_comment', 'delete_own_comment',
                'view_tag',
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

            if ($role === null) {
                continue;
            }

            if ($config['all'] === true) {
                // Super Admin gets all permissions via optimized query call
                $role->permissions()->sync(\App\Models\Permission::pluck('id'));
            } else {
                $permissions = [];

                // Add permissions from groups
                foreach ($config['groups'] as $group) {
                    $permissions = array_merge($permissions, self::PERMISSION_GROUPS[$group]);
                }

                // Add specific permissions
                $permissions = array_merge($permissions, $config['permissions']);

                // Sync permissions to role
                $role->permissions()->sync(
                    \App\Models\Permission::whereIn('name', array_unique($permissions))->pluck('id')
                );
            }
        }
    }
}
