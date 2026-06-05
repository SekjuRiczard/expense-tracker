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

namespace App\Budget\Provider;

use App\Budget\Entity\Budget;
use App\Budget\Exception\BudgetException;
use App\Budget\Repository\BudgetRepository;
use App\Entity\User;

final readonly class BudgetResourceProvider
{
    public function __construct(
        private BudgetRepository $budgetRepository,
    ) {
    }

    public function getBudget(int $id, User $user): Budget
    {
        return $this->budgetRepository->findSingleByUser($id, $user)
            ?? throw BudgetException::notFound();
    }
}