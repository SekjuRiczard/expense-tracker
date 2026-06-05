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

final class InvalidPasswordChangeException extends RuntimeException
{
    public static function invalidCurrentPassword(): self
    {
        return new self('Current password is invalid.');
    }

    public static function passwordsDoNotMatch(): self
    {
        return new self('New password and confirmation do not match.');
    }

    public static function sameAsCurrentPassword(): self
    {
        return new self('New password must be different from current password.');
    }
}
