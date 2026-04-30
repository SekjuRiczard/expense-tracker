<?php

declare(strict_types=1);

namespace App\Exception;

use DomainException;

class UserAlreadyExistsException extends DomainException
{
    public static function forEmail(string $email): self
    {
        return new self(sprintf('User with this email ("%s") already exists.', $email), 409);
    }
}