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

namespace App\Budget\Action;

use App\Analytics\Service\BudgetUsageCalculator;
use App\Budget\Dto\Response\BudgetOverviewResponse;
use App\Budget\Entity\Budget;
use App\Budget\Repository\BudgetRepository;
use App\Entity\User;

final readonly class ListBudgetsOverviewAction
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private BudgetUsageCalculator $budgetUsageCalculator,
    ) {
    }

    /**
     * @return list<BudgetOverviewResponse>
     */
    public function execute(User $user): array
    {
        return array_map(
            fn (Budget $budget): BudgetOverviewResponse => BudgetOverviewResponse::fromEntity(
                $budget,
                $this->budgetUsageCalculator->calculate($budget),
            ),
            $this->budgetRepository->findByUser($user),
        );
    }
}
