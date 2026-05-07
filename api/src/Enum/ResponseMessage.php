<?php

declare(strict_types=1);

namespace App\Enum;

enum ResponseMessage: string
{
    case LOGIN_SUCCESS = 'Password verified. PIN verification required.';
    case PIN_SETUP_REQUIRED = 'Password verified. PIN setup required.';
    case REGISTER_SUCCESS = 'User created. PIN setup required.';
    case AUTH_COMPLETE = 'Authentication successful.';
    case SESSION_REFRESHED = 'Session refreshed successfully.';

    public function t(): string
    {
        return $this->value;
    }
}