<?php

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