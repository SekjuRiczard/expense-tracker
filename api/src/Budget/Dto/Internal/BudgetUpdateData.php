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

namespace App\Budget\Dto\Internal;

use App\Budget\Enum\BudgetPeriodType;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;

final readonly class BudgetUpdateData
{
    public function __construct(
        public string $name,
        public int $amount,
        public CurrencyCode $currency,
        public BudgetPeriodType $periodType,
        public DateTimeImmutable $startDate,
        public DateTimeImmutable $endDate,
    ) {
    }
}