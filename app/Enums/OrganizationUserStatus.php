<?php

namespace App\Enums;

enum OrganizationUserStatus: string
{
    case ACTIVE = 'active';

    case PENDING = 'pending';

    case REJECTED = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
