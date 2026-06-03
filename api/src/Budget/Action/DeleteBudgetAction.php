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

use App\Budget\Entity\Budget;
use App\Budget\Provider\BudgetResourceProvider;
use App\Budget\Repository\BudgetRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DeleteBudgetAction
{
    public function __construct(
        private BudgetResourceProvider $resourceProvider,
        private BudgetRepository $budgetRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(int $id, User $user): void
    {
        /** @var Budget $budget */
        $budget = $this->resourceProvider->getBudget($id, $user);
        $this->budgetRepository->remove($budget);
        $this->entityManager->flush();
    }
}