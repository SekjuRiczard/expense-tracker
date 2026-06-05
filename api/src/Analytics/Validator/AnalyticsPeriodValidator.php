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

namespace App\Analytics\Validator;

use App\Analytics\Exception\AnalyticsException;
use DateTimeImmutable;

final readonly class AnalyticsPeriodValidator
{
    public function validate(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
    ): void {
        if ($from > $to) {
            throw AnalyticsException::invalidDateRange();
        }
    }
}