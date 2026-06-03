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

namespace App\Budget\Validator;

use App\Budget\Enum\BudgetPeriodType;
use App\Budget\Exception\BudgetException;
use DateTimeImmutable;

final readonly class BudgetValidator
{
    public function validatePeriod(BudgetPeriodType $periodType, DateTimeImmutable $startDate, DateTimeImmutable $endDate): void
    {
        if ($startDate > $endDate) {
            throw BudgetException::invalidDateRange();
        }
        match ($periodType) {
            BudgetPeriodType::MONTHLY => $this->validateMonthlyPeriod($startDate, $endDate),
            BudgetPeriodType::YEARLY => $this->validateYearlyPeriod($startDate, $endDate),
            BudgetPeriodType::CUSTOM => null,
        };
    }

    private function validateMonthlyPeriod(DateTimeImmutable $startDate, DateTimeImmutable $endDate): void
    {
        if ($startDate->format('d') !== '01' || $endDate->format('Y-m-d') !== $startDate->format('Y-m-t')) {
            throw BudgetException::invalidMonthlyPeriod();
        }
    }

    private function validateYearlyPeriod(DateTimeImmutable $startDate, DateTimeImmutable $endDate): void
    {
        if ($startDate->format('m-d') !== '01-01' || $endDate->format('Y-m-d') !== $startDate->format('Y-12-31')) {
            throw BudgetException::invalidYearlyPeriod();
        }
    }
}