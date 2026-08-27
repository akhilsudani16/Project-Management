<?php

namespace App\Enums;

enum ActivityLog: string
{
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case CREATED_PROJECT = 'created_project';
    case UPDATED_PROJECT = 'updated_project';
    case DELETED_PROJECT = 'deleted_project';
    case CREATED_TASK = 'created_task';
    case UPDATED_TASK = 'updated_task';
    case COMPLETED_TASK = 'completed_task';
    case COMMENTED = 'commented';
    case UPLOADED_ATTACHMENT = 'uploaded_attachment';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
