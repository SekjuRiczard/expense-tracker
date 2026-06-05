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

namespace App\Analytics\Service;

use App\Analytics\Dto\Internal\BudgetUsageData;
use App\Analytics\Repository\BudgetExpenseReaderInterface;
use App\Budget\Entity\Budget;
use LogicException;

final readonly class BudgetUsageCalculator
{
    public function __construct(
        private BudgetExpenseReaderInterface $budgetExpenseReader,
    ) {
    }

    public function calculate(Budget $budget): BudgetUsageData
    {
        $this->assertPositiveBudgetAmount($budget);
        /** @var int $spent */
        $spent = $this->budgetExpenseReader->sumExpensesForPeriod(
            user: $budget->getUser(),
            currency: $budget->getCurrency(),
            startDate: $budget->getStartDate(),
            endDateExclusive: $budget->getEndDate()->modify('+1 day'),
        );

        return new BudgetUsageData(
            spent: $spent,
            remaining: $budget->getAmount() - $spent,
            percentage: round(
                $spent / $budget->getAmount() * 100,
                2,
            ),
            exceeded: $spent > $budget->getAmount(),
        );
    }

    private function assertPositiveBudgetAmount(Budget $budget): void
    {
        if (0 >= $budget->getAmount()) {
            throw new LogicException(
                'Budget amount must be greater than zero.',
            );
        }
    }
}