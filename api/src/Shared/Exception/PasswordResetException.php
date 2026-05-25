<?php

declare(strict_types=1);

namespace App\Shared\Exception;

use RuntimeException;

final class PasswordResetException extends RuntimeException
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