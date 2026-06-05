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

namespace App\Analytics\Action;

use App\Analytics\Dto\Response\BudgetUsageResponse;
use App\Analytics\Service\BudgetUsageCalculator;
use App\Budget\Entity\Budget;
use App\Budget\Provider\BudgetResourceProvider;
use App\Entity\User;

final readonly class GetBudgetUsageAction
{
    public function __construct(
        private BudgetResourceProvider $budgetResourceProvider,
        private BudgetUsageCalculator $budgetUsageCalculator,
    ) {
    }

    public function execute(int $budgetId, User $user): BudgetUsageResponse
    {
        /** @var Budget $budget */
        $budget = $this->budgetResourceProvider->getBudget(
            $budgetId,
            $user,
        );

        return BudgetUsageResponse::create(
            budget: $budget,
            usage: $this->budgetUsageCalculator->calculate($budget),
        );
    }
}