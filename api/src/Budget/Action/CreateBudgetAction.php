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

use App\Budget\Dto\Request\CreateBudgetRequest;
use App\Budget\Dto\Response\BudgetResponse;
use App\Budget\Entity\Budget;
use App\Budget\Exception\BudgetAlreadyExistsException;
use App\Budget\Repository\BudgetRepository;
use App\Budget\Validator\BudgetValidator;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CreateBudgetAction
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private BudgetValidator $budgetValidator,
        private EntityManagerInterface $entityManager,
    ) {}

    public function execute(CreateBudgetRequest $request, User $user): BudgetResponse
    {
        $this->budgetValidator->validatePeriod($request->periodType, $request->startDate, $request->endDate);
        if ($this->budgetRepository->existsForPeriod($user, $request->currency, $request->periodType, $request->startDate, $request->endDate)) {
            throw new BudgetAlreadyExistsException();
        }
        /** @var Budget $budget */
        $budget = new Budget($user, $request->name, $request->amount, $request->currency, $request->periodType, $request->startDate, $request->endDate);
        $this->budgetRepository->add($budget);
        $this->entityManager->flush();

        return BudgetResponse::fromEntity($budget);
    }
}