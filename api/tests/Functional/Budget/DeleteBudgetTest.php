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

namespace App\Tests\Functional\Budget;

use App\Tests\Support\BudgetFunctionalTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class DeleteBudgetTest extends BudgetFunctionalTestCase
{
    public function testAuthenticatedUserCanDeleteOwnBudget(): void
    {
        $user = $this->authenticateUser();

        $budget = $this->createBudget(user: $user);

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->deleteJson(
            sprintf('/api/budgets/%d', $budgetId),
        );

        self::assertSame(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode(),
        );
        self::assertSame('', $response->getContent());
        self::assertNull($this->findBudgetFresh($budgetId));
    }

    public function testAuthenticatedUserCannotDeleteAnotherUserBudget(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $budget = $this->createBudget(user: $owner);

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $this->authenticateUser(
            email: 'intruder@example.com',
            username: 'intruder',
        );

        $response = $this->deleteJson(
            sprintf('/api/budgets/%d', $budgetId),
        );

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
        );
        self::assertNotNull($this->findBudgetFresh($budgetId));
    }

    public function testDeleteMissingBudgetReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->deleteJson('/api/budgets/999999');

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotDeleteBudget(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $budget = $this->createBudget(user: $owner);

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->deleteJson(
            sprintf('/api/budgets/%d', $budgetId),
        );

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
        );
        self::assertNotNull($this->findBudgetFresh($budgetId));
    }

    public function testDeletingOneBudgetDoesNotDeleteAnotherBudget(): void
    {
        $user = $this->authenticateUser();

        $budgetToDelete = $this->createBudget(
            user: $user,
            name: 'Do usunięcia',
        );

        $budgetToKeep = $this->createBudget(
            user: $user,
            name: 'Ma zostać',
            startDate: new DateTimeImmutable('2026-07-01'),
            endDate: new DateTimeImmutable('2026-07-31'),
        );

        $budgetToDeleteId = $budgetToDelete->getId();
        $budgetToKeepId = $budgetToKeep->getId();

        self::assertIsInt($budgetToDeleteId);
        self::assertIsInt($budgetToKeepId);

        $response = $this->deleteJson(
            sprintf('/api/budgets/%d', $budgetToDeleteId),
        );

        self::assertSame(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode(),
        );
        self::assertNull($this->findBudgetFresh($budgetToDeleteId));
        self::assertNotNull($this->findBudgetFresh($budgetToKeepId));
    }
}