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

namespace App\Shared\Exception;

use RuntimeException;

final class TooManyPasswordResetAttemptsException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Too many password reset attempts. Try again later.');
    }
}
