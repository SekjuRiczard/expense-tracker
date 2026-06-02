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

namespace App\Category\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class CategoryException extends HttpException
{
    public static function notFound(): self
    {
        return new self(
            statusCode: Response::HTTP_NOT_FOUND,
            message: 'Category not found.',
        );
    }

    public static function nothingToUpdate(): self
    {
        return new self(
            statusCode: Response::HTTP_BAD_REQUEST,
            message: 'No category data provided for update.',
        );
    }

    public static function hasTransactions(): self
    {
        return new self(
            statusCode: Response::HTTP_CONFLICT,
            message: 'Category has transactions and cannot be deleted.',
        );
    }
}