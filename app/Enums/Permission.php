<?php

namespace App\Enums;

enum Permission: string
{
    case CREATE_ORGANIZATION = 'create_organization';
    case UPDATE_ORGANIZATION = 'update_organization';
    case VIEW_ORGANIZATION = 'view_organization';
    case DELETE_ORGANIZATION = 'delete_organization';

    case CREATE_PROJECT = 'create_project';
    case UPDATE_PROJECT = 'update_project';
    case VIEW_PROJECT = 'view_project';
    case DELETE_PROJECT = 'delete_project';
    case ASSIGN_PROJECT_USER = 'assign_project_user';
    case REMOVE_PROJECT_USER = 'remove_project_user';
    case VIEW_OWN_PROJECT = 'view_own_project';
    case UPDATE_OWN_PROJECT = 'update_own_project';

    case CREATE_TASK = 'create_task';
    case UPDATE_TASK = 'update_task';
    case VIEW_TASK = 'view_task';
    case DELETE_TASK = 'delete_task';
    case ASSIGN_TASK = 'assign_task';
    case VIEW_TEAM_TASK = 'view_team_task';
    case VIEW_OWN_TASK = 'view_own_task';
    case UPDATE_OWN_TASK = 'update_own_task';
    case VIEW_ASSIGNED_TASK = 'view_assigned_task';
    case UPDATE_ASSIGNED_TASK = 'update_assigned_task';

    case CREATE_USER = 'create_user';
    case UPDATE_USER = 'update_user';
    case VIEW_USER = 'view_user';
    case DELETE_USER = 'delete_user';

    case CREATE_COMMENT = 'create_comment';
    case UPDATE_COMMENT = 'update_comment';
    case DELETE_COMMENT = 'delete_comment';
    case UPDATE_OWN_COMMENT = 'update_own_comment';
    case DELETE_OWN_COMMENT = 'delete_own_comment';

    case CREATE_TAG = 'create_tag';
    case UPDATE_TAG = 'update_tag';
    case DELETE_TAG = 'delete_tag';
    case VIEW_TAG = 'view_tag';
    case ATTACH_TAG = 'attach_tag';

    case UPLOAD_ATTACHMENT = 'upload_attachment';
    case DELETE_ATTACHMENT = 'delete_attachment';
    case VIEW_ATTACHMENT = 'view_attachment';
    case DELETE_OWN_ATTACHMENT = 'delete_own_attachment';

    case VIEW_ACTIVITY_LOG = 'view_activity_log';
    case VIEW_OWN_ACTIVITY_LOG = 'view_own_activity_log';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function organizationPermissions(): array
    {
        return [
            self::CREATE_ORGANIZATION,
            self::UPDATE_ORGANIZATION,
            self::VIEW_ORGANIZATION,
            self::DELETE_ORGANIZATION,
        ];
    }

    public static function projectPermissions(): array
    {
        return [
            self::CREATE_PROJECT,
            self::UPDATE_PROJECT,
            self::VIEW_PROJECT,
            self::DELETE_PROJECT,
            self::ASSIGN_PROJECT_USER,
            self::REMOVE_PROJECT_USER,
            self::VIEW_OWN_PROJECT,
            self::UPDATE_OWN_PROJECT,
        ];
    }

    public static function taskPermissions(): array
    {
        return [
            self::CREATE_TASK,
            self::UPDATE_TASK,
            self::VIEW_TASK,
            self::DELETE_TASK,
            self::ASSIGN_TASK,
            self::VIEW_TEAM_TASK,
            self::VIEW_OWN_TASK,
            self::UPDATE_OWN_TASK,
            self::VIEW_ASSIGNED_TASK,
            self::UPDATE_ASSIGNED_TASK,
        ];
    }

    public static function userPermissions(): array
    {
        return [
            self::CREATE_USER,
            self::UPDATE_USER,
            self::VIEW_USER,
            self::DELETE_USER,
        ];
    }

    public static function commentPermissions(): array
    {
        return [
            self::CREATE_COMMENT,
            self::UPDATE_COMMENT,
            self::DELETE_COMMENT,
            self::UPDATE_OWN_COMMENT,
            self::DELETE_OWN_COMMENT,
        ];
    }

    public static function tagPermissions(): array
    {
        return [
            self::CREATE_TAG,
            self::UPDATE_TAG,
            self::DELETE_TAG,
            self::VIEW_TAG,
            self::ATTACH_TAG,
        ];
    }

    public static function attachmentPermissions(): array
    {
        return [
            self::UPLOAD_ATTACHMENT,
            self::DELETE_ATTACHMENT,
            self::VIEW_ATTACHMENT,
            self::DELETE_OWN_ATTACHMENT,
        ];
    }

    public static function activityLogPermissions(): array
    {
        return [
            self::VIEW_ACTIVITY_LOG,
            self::VIEW_OWN_ACTIVITY_LOG,
        ];
    }

    public static function allPermissions(): array
    {
        return array_merge(
            self::organizationPermissions(),
            self::projectPermissions(),
            self::taskPermissions(),
            self::userPermissions(),
            self::commentPermissions(),
            self::tagPermissions(),
            self::attachmentPermissions(),
            self::activityLogPermissions()
        );
    }
}
