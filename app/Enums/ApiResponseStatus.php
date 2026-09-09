<?php

namespace App\Enums;

enum ApiResponseStatus: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case FAIL = 'fail';
}
