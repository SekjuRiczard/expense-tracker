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

final class PasswordResetException extends \RuntimeException
{
    public static function invalidOrExpiredCode(): self
    {
        return new self('Invalid or expired password reset code.');
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
