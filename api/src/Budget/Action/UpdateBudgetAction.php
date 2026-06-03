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

use App\Budget\Dto\Internal\BudgetUpdateData;
use App\Budget\Dto\Request\UpdateBudgetRequest;
use App\Budget\Dto\Response\BudgetResponse;
use App\Budget\Entity\Budget;
use App\Budget\Exception\BudgetAlreadyExistsException;
use App\Budget\Provider\BudgetResourceProvider;
use App\Budget\Repository\BudgetRepository;
use App\Budget\Resolver\BudgetUpdateResolver;
use App\Budget\Validator\BudgetValidator;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class UpdateBudgetAction
{
    public function __construct(
        private BudgetResourceProvider $resourceProvider,
        private BudgetUpdateResolver $updateResolver,
        private BudgetValidator $budgetValidator,
        private BudgetRepository $budgetRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function execute(int $id, UpdateBudgetRequest $request, User $user): BudgetResponse
    {
        /** @var Budget $budget */
        $budget = $this->resourceProvider->getBudget($id, $user);
        /** @var BudgetUpdateData $data */
        $data = $this->updateResolver->resolve($budget, $request);
        $this->budgetValidator->validatePeriod($data->periodType, $data->startDate, $data->endDate);
        $this->assertBudgetDoesNotConflict($budget, $data, $user);
        $budget->update($data->name, $data->amount, $data->currency, $data->periodType, $data->startDate, $data->endDate);
        $this->entityManager->flush();

        return BudgetResponse::fromEntity($budget);
    }

    private function assertBudgetDoesNotConflict(Budget $budget, BudgetUpdateData $data, User $user): void
    {
        if ($this->budgetRepository->existsForPeriodExceptBudget($budget, $user, $data->currency, $data->periodType, $data->startDate, $data->endDate)) {
            throw new BudgetAlreadyExistsException();
        }
    }
}