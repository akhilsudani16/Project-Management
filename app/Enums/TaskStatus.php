<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';

    case REVIEW = 'review';
    case DONE = 'done';

    case COMPLETED = 'completed';

    // TODO: add reopen status
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
