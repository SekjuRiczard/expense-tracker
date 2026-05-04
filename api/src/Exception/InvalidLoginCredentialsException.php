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

namespace App\Exception;

use RuntimeException;

final class InvalidLoginCredentialsException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Invalid email or password.');
    }
}