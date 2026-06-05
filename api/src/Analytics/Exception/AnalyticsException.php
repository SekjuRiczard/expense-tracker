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

namespace App\Analytics\Exception;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class AnalyticsException
{
    public static function invalidDateRange(): UnprocessableEntityHttpException
    {
        return new UnprocessableEntityHttpException(
            'The analytics start date must be earlier than or equal to the end date.',
        );
    }
}