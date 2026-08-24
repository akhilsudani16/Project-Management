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
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));

        // Organization Admin permissions
        $orgAdminPermissions = [
            // Organization
            'create_organization', 'update_organization', 'view_organization', 'delete_organization',
            // Project
            'create_project', 'update_project', 'view_project', 'delete_project', 'assign_project_user', 'remove_project_user',
            // Task
            'create_task', 'update_task', 'view_task', 'delete_task', 'assign_task', 'view_team_task',
            // User
            'create_user', 'update_user', 'view_user', 'delete_user',
            // Comment
            'create_comment', 'update_comment', 'delete_comment', 'moderate_comment',
            // Tag
            'create_tag', 'update_tag', 'delete_tag', 'view_tag', 'attach_tag',
            // Attachment
            'upload_attachment', 'delete_attachment', 'view_attachment',
            // Activity Log
            'view_activity_log',
        ];
        $orgAdminPermissionIds = \App\Models\Permission::whereIn('name', $orgAdminPermissions)->pluck('id');
        $orgAdminRole->permissions()->sync($orgAdminPermissionIds);

        // Project Manager permissions
        $pmPermissions = [
            // Project
            'view_project', 'update_own_project', 'assign_project_user',
            // Task
            'create_task', 'update_task', 'view_task', 'delete_task', 'assign_task', 'view_team_task',
            // User
            'view_user',
            // Comment
            'create_comment', 'update_own_comment', 'delete_own_comment', 'moderate_comment',
            // Tag
            'view_tag', 'attach_tag',
            // Attachment
            'upload_attachment', 'delete_own_attachment', 'view_attachment',
            // Activity Log
            'view_activity_log',
        ];
        $pmPermissionIds = \App\Models\Permission::whereIn('name', $pmPermissions)->pluck('id');
        $projectManagerRole->permissions()->sync($pmPermissionIds);

        // Member permissions
        $memberPermissions = [
            // Project
            'view_own_project',
            // Task
            'view_own_task', 'update_own_task', 'view_assigned_task', 'update_assigned_task', 'view_team_task',
            // User
            'view_user',
            // Comment
            'create_comment', 'update_own_comment', 'delete_own_comment',
            // Tag
            'view_tag',
            // Attachment
            'upload_attachment', 'delete_own_attachment', 'view_attachment',
            // Activity Log
            'view_own_activity_log',
        ];
        $memberPermissionIds = \App\Models\Permission::whereIn('name', $memberPermissions)->pluck('id');
        $memberRole->permissions()->sync($memberPermissionIds);
    }
}
