<?php

namespace App\Enums;

enum Permission: string
{
    case CREATE_PROJECT = 'create_project';
    case UPDATE_PROJECT = 'update_project';
    case DELETE_PROJECT = 'delete_project';
    case VIEW_PROJECT = 'view_project';

    case CREATE_TASK = 'create_task';
    case UPDATE_TASK = 'update_task';
    case DELETE_TASK = 'delete_task';
    case VIEW_TASK = 'view_task';

    case CREATE_USER = 'create_user';
    case UPDATE_USER = 'update_user';
    case DELETE_USER = 'delete_user';
    case VIEW_USER = 'view_user';

    case CREATE_ORGANIZATION = 'create_organization';
    case UPDATE_ORGANIZATION = 'update_organization';
    case DELETE_ORGANIZATION = 'delete_organization';
    case VIEW_ORGANIZATION = 'view_organization';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
