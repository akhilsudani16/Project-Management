<?php

namespace App\Enums;

enum ActivityLog: string
{
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case FAILED_LOGIN = 'failed_login';
    case PASSWORD_CHANGED = 'password_changed';
    case PASSWORD_RESET = 'password_reset';
    case EMAIL_VERIFIED = 'email_verified';
    case SESSION_REVOKED = 'session_revoked';
    case INVITATION_SENT = 'invitation_sent';
    case INVITATION_ACCEPTED = 'invitation_accepted';
    case MEMBERSHIP_CHANGED = 'membership_changed';
    case ROLE_CHANGED = 'role_changed';
    case ORGANIZATION_CREATED = 'organization_created';
    case ORGANIZATION_ARCHIVED = 'organization_archived';
    case CREATED_PROJECT = 'created_project';
    case UPDATED_PROJECT = 'updated_project';
    case DELETED_PROJECT = 'deleted_project';
    case PROJECT_ARCHIVED = 'project_archived';
    case PROJECT_RESTORED = 'project_restored';
    case CREATED_TASK = 'created_task';
    case UPDATED_TASK = 'updated_task';
    case COMPLETED_TASK = 'completed_task';
    case TASK_ASSIGNED = 'task_assigned';
    case COMMENTED = 'commented';
    case UPLOADED_ATTACHMENT = 'uploaded_attachment';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
