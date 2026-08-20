<?php

namespace App\Enums;

enum OrganizationStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case archived = 'archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
