<?php

/*
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Wallet\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class WalletException extends HttpException
{
    public static function notFound(): self
    {
        return new self(Response::HTTP_NOT_FOUND, 'Wallet not found.');
    }

    public static function emptyUpdateRequest(): self
    {
        return new self(Response::HTTP_BAD_REQUEST, 'At least one wallet field must be provided.');
    }

    public static function hasTransactions(): self
    {
        return new self(Response::HTTP_CONFLICT, 'Wallet has transactions and cannot be deleted.');
    }
}