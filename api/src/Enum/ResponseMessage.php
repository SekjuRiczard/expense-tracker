<?php

/**
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

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
