<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

final class UnauthenticatedUserException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unauthorized.');
    }
}
