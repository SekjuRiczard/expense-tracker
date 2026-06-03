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

use App\Budget\Dto\Response\BudgetResponse;
use App\Budget\Entity\Budget;
use App\Budget\Repository\BudgetRepository;
use App\Entity\User;

final readonly class ListBudgetsAction
{
    public function __construct(
        private BudgetRepository $budgetRepository,
    ) {
    }

    /**
     * @return list<BudgetResponse>
     */
    public function execute(User $user): array
    {
        return array_map(
            static fn (Budget $budget): BudgetResponse => BudgetResponse::fromEntity($budget),
            $this->budgetRepository->findByUser($user),
        );
    }
}