<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case ONHOLD = 'onhold';
    case ARCHIVED = 'archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
