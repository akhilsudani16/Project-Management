<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ORGANIZATION_ADMIN = 'organization_admin';
    case PROJECT_MANAGER = 'project_manager';
    case MEMBER = 'member';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
