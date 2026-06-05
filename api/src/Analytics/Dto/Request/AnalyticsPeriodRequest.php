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

namespace App\Analytics\Dto\Request;

use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;

final readonly class AnalyticsPeriodRequest
{
    public function __construct(
        public DateTimeImmutable $from,
        public DateTimeImmutable $to,
        public CurrencyCode $currency,
    ) {
    }
}