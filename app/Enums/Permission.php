<?php

namespace App\Enums;

enum Permission: string
{
    case CREATE_ORGANIZATION = 'create_organization';
    case UPDATE_ORGANIZATION = 'update_organization';
    case VIEW_ORGANIZATION = 'view_organization';
    case ARCHIVE_ORGANIZATION = 'archive_organization';
    case DELETE_ORGANIZATION = 'delete_organization';

    case CREATE_PROJECT = 'create_project';
    case UPDATE_PROJECT = 'update_project';
    case VIEW_PROJECT = 'view_project';
    case DELETE_PROJECT = 'delete_project';
    case ASSIGN_PROJECT_USER = 'assign_project_user';
    case REMOVE_PROJECT_USER = 'remove_project_user';

    case CREATE_TASK = 'create_task';
    case UPDATE_TASK = 'update_task';
    case VIEW_TASK = 'view_task';
    case DELETE_TASK = 'delete_task';
    case ASSIGN_TASK = 'assign_task';

    case CREATE_USER = 'create_user';
    case UPDATE_USER = 'update_user';
    case VIEW_USER = 'view_user';
    case DELETE_USER = 'delete_user';

    case CREATE_COMMENT = 'create_comment';
    case UPDATE_COMMENT = 'update_comment';
    case DELETE_COMMENT = 'delete_comment';

    case CREATE_TAG = 'create_tag';
    case UPDATE_TAG = 'update_tag';
    case DELETE_TAG = 'delete_tag';
    case VIEW_TAG = 'view_tag';
    case ATTACH_TAG = 'attach_tag';

    case UPLOAD_ATTACHMENT = 'upload_attachment';
    case DELETE_ATTACHMENT = 'delete_attachment';
    case VIEW_ATTACHMENT = 'view_attachment';

    case VIEW_ACTIVITY_LOG = 'view_activity_log';

    case INVITE_PROJECT_MANAGER = 'invite_project_manager';
    case INVITE_MEMBER = 'invite_member';
    case INVITE_SUPER_ADMIN = 'invite_super_admin';
    case INVITE_ORGANIZATION_ADMIN = 'invite_organization_admin';
    case ARCHIVE_PROJECT = 'archive_project';
    case RESTORE_PROJECT = 'restore_project';
    case UPDATE_ASSIGNED_TASK = 'update_assigned_task';
    case VIEW_ASSIGNED_TASK = 'view_assigned_task';
    case VIEW_PROFILE = 'view_profile';
    case UPDATE_PROFILE = 'update_profile';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function organizationAdminPermissions(): array
    {
        return [
            self::VIEW_ORGANIZATION,
            self::UPDATE_ORGANIZATION,
            self::INVITE_PROJECT_MANAGER,
            self::INVITE_MEMBER,
            self::CREATE_PROJECT,
            self::UPDATE_PROJECT,
            self::VIEW_PROJECT,
            self::DELETE_PROJECT,
            self::ASSIGN_PROJECT_USER,
            self::REMOVE_PROJECT_USER,
            self::CREATE_TASK,
            self::UPDATE_TASK,
            self::VIEW_TASK,
            self::DELETE_TASK,
            self::ASSIGN_TASK,
            self::VIEW_USER,
            self::CREATE_USER,
            self::UPDATE_USER,
            self::CREATE_COMMENT,
            self::UPDATE_COMMENT,
            self::DELETE_COMMENT,
            self::CREATE_TAG,
            self::UPDATE_TAG,
            self::DELETE_TAG,
            self::VIEW_TAG,
            self::ATTACH_TAG,
            self::UPLOAD_ATTACHMENT,
            self::DELETE_ATTACHMENT,
            self::VIEW_ATTACHMENT,
            self::VIEW_ACTIVITY_LOG,
            self::VIEW_PROFILE,
            self::UPDATE_PROFILE,
        ];
    }

    public static function projectManagerPermissions(): array
    {
        return [
            self::VIEW_PROJECT,
            self::ASSIGN_PROJECT_USER,
            self::REMOVE_PROJECT_USER,
            self::CREATE_TASK,
            self::UPDATE_TASK,
            self::VIEW_TASK,
            self::DELETE_TASK,
            self::ASSIGN_TASK,
            self::VIEW_USER,
            self::CREATE_COMMENT,
            self::VIEW_TAG,
            self::ATTACH_TAG,
            self::UPLOAD_ATTACHMENT,
            self::VIEW_ATTACHMENT,
            self::VIEW_ACTIVITY_LOG,
            self::VIEW_PROFILE,
            self::UPDATE_PROFILE,
        ];
    }

    public static function memberPermissions(): array
    {
        return [
            self::VIEW_PROJECT,
            self::VIEW_TASK,
            self::UPDATE_TASK,
            self::VIEW_USER,
            self::CREATE_COMMENT,
            self::UPDATE_COMMENT,
            self::DELETE_COMMENT,
            self::VIEW_TAG,
            self::ATTACH_TAG,
            self::UPLOAD_ATTACHMENT,
            self::DELETE_ATTACHMENT,
            self::VIEW_ATTACHMENT,
            self::VIEW_ACTIVITY_LOG,
            self::VIEW_PROFILE,
            self::UPDATE_PROFILE,
        ];
    }
}
