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

namespace App\Analytics\Repository;

use App\Entity\User;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;

interface BudgetExpenseReaderInterface
{
    public function sumExpensesForPeriod(
        User $user,
        CurrencyCode $currency,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDateExclusive,
    ): int;
}