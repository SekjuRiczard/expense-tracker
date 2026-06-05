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

namespace App\Analytics\Dto\Response;

use App\Analytics\Dto\Internal\BudgetUsageData;
use App\Budget\Entity\Budget;
use LogicException;

final readonly class BudgetUsageResponse
{
    public function __construct(
        public int $budgetId,
        public string $budgetName,
        public int $budgetAmount,
        public string $currency,
        public string $startDate,
        public string $endDate,
        public int $spent,
        public int $remaining,
        public float $percentage,
        public bool $exceeded,
    ) {
    }

    public static function create(
        Budget $budget,
        BudgetUsageData $usage,
    ): self {
        return new self(
            budgetId: $budget->getId()
            ?? throw new LogicException('Budget ID is missing.'),
            budgetName: $budget->getName(),
            budgetAmount: $budget->getAmount(),
            currency: $budget->getCurrency()->value,
            startDate: $budget->getStartDate()->format('Y-m-d'),
            endDate: $budget->getEndDate()->format('Y-m-d'),
            spent: $usage->spent,
            remaining: $usage->remaining,
            percentage: $usage->percentage,
            exceeded: $usage->exceeded,
        );
    }
}