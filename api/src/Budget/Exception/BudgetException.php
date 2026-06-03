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

namespace App\Budget\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class BudgetException
{
    public static function notFound(): NotFoundHttpException
    {
        return new NotFoundHttpException('Budget not found.');
    }

    public static function nothingToUpdate(): BadRequestHttpException
    {
        return new BadRequestHttpException('No budget fields to update.');
    }

    public static function invalidDateRange(): UnprocessableEntityHttpException
    {
        return new UnprocessableEntityHttpException(
            'The budget start date must be earlier than or equal to the end date.',
        );
    }

    public static function invalidMonthlyPeriod(): UnprocessableEntityHttpException
    {
        return new UnprocessableEntityHttpException(
            'A monthly budget must cover exactly one full calendar month.',
        );
    }

    public static function invalidYearlyPeriod(): UnprocessableEntityHttpException
    {
        return new UnprocessableEntityHttpException(
            'A yearly budget must cover exactly one full calendar year.',
        );
    }
}