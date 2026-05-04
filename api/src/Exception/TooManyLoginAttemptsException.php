<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

final class TooManyLoginAttemptsException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Too many login attempts. Try again later.');
    }
}