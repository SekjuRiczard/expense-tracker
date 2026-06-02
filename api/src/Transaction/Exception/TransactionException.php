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

namespace App\Transaction\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class TransactionException extends HttpException
{
    public static function notFound(): self
    {
        return new self(Response::HTTP_NOT_FOUND, 'Transaction not found.');
    }

    public static function walletNotFound(): self
    {
        return new self(Response::HTTP_NOT_FOUND, 'Wallet not found.');
    }

    public static function categoryNotFound(): self
    {
        return new self(Response::HTTP_NOT_FOUND, 'Category not found.');
    }

    public static function categoryTypeMismatch(): self
    {
        return new self(Response::HTTP_UNPROCESSABLE_ENTITY, 'Category type does not match transaction type.');
    }

    public static function nothingToUpdate(): self
    {
        return new self(Response::HTTP_BAD_REQUEST, 'At least one transaction field must be provided.');
    }
}
