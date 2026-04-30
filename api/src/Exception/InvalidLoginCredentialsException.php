<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

final class InvalidLoginCredentialsException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Invalid email or password.');
    }
}