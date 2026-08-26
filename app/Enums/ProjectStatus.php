<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case ON_HOLD = 'onhold';
    case ARCHIVED = 'archived';
    case DELETED = 'deleted';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
