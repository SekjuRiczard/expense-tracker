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

namespace App\Budget\Dto\Response;

use App\Analytics\Dto\Internal\BudgetUsageData;
use App\Budget\Entity\Budget;

final readonly class BudgetOverviewResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public int $amount,
        public string $currency,
        public string $periodType,
        public string $startDate,
        public string $endDate,
        public int $spentAmount,
        public int $remainingAmount,
        public float $percentage,
        public string $status,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(
        Budget $budget,
        BudgetUsageData $usage,
    ): self {
        return new self(
            id: $budget->getId()
                ?? throw new \LogicException('Budget ID is missing.'),
            name: $budget->getName(),
            amount: $budget->getAmount(),
            currency: $budget->getCurrency()->value,
            periodType: $budget->getPeriodType()->value,
            startDate: $budget->getStartDate()->format('Y-m-d'),
            endDate: $budget->getEndDate()->format('Y-m-d'),
            spentAmount: $usage->spent,
            remainingAmount: $usage->remaining,
            percentage: $usage->percentage,
            status: self::resolveStatus($usage->percentage),
            createdAt: $budget->getCreatedAt()->format(DATE_ATOM),
            updatedAt: $budget->getUpdatedAt()->format(DATE_ATOM),
        );
    }

    private static function resolveStatus(float $percentage): string
    {
        return match (true) {
            $percentage > 100.0 => 'exceeded',
            $percentage >= 80.0 => 'warning',
            default => 'ok',
        };
    }
}
