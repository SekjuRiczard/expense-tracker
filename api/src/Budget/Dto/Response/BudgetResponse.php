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

use App\Budget\Entity\Budget;
use LogicException;

final readonly class BudgetResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public int $amount,
        public string $currency,
        public string $periodType,
        public string $startDate,
        public string $endDate,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(Budget $budget): self
    {
        return new self(
            id: $budget->getId()
            ?? throw new LogicException('Budget ID is missing.'),
            name: $budget->getName(),
            amount: $budget->getAmount(),
            currency: $budget->getCurrency()->value,
            periodType: $budget->getPeriodType()->value,
            startDate: $budget->getStartDate()->format('Y-m-d'),
            endDate: $budget->getEndDate()->format('Y-m-d'),
            createdAt: $budget->getCreatedAt()->format(DATE_ATOM),
            updatedAt: $budget->getUpdatedAt()->format(DATE_ATOM),
        );
    }
}