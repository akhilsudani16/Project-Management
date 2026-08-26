<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';

    case REVIEW = 'review';
    case DONE = 'done';

    case COMPLETED = 'completed';
    case REOPENED = 'reopened';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
