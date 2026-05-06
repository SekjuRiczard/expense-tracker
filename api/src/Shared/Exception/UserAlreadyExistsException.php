<?php

/*
 * This file is part of the Expense Tracker.
 *
 * (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Shared\Exception;

use DomainException;

class UserAlreadyExistsException extends DomainException
{
    public static function forEmail(string $email): self
    {
        return new self(sprintf('User with this email ("%s") already exists.', $email), 409);
    }
}