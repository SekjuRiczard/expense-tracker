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

namespace App\Tests\Support;

use App\Budget\Entity\Budget;
use App\Budget\Enum\BudgetPeriodType;
use App\Entity\User;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;

abstract class AnalyticsFunctionalTestCase extends TransactionFunctionalTestCase
{
    protected function createBudget(
        User $user,
        string $name = 'Budżet domowy',
        int $amount = 300000,
        CurrencyCode $currency = CurrencyCode::PLN,
        BudgetPeriodType $periodType = BudgetPeriodType::MONTHLY,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null,
    ): Budget {
        /** @var Budget $budget */
        $budget = new Budget(
            user: $user,
            name: $name,
            amount: $amount,
            currency: $currency,
            periodType: $periodType,
            startDate: $startDate ?? new DateTimeImmutable('2026-06-01'),
            endDate: $endDate ?? new DateTimeImmutable('2026-06-30'),
        );

        $this->entityManager->persist($budget);
        $this->entityManager->flush();

        return $budget;
    }
}