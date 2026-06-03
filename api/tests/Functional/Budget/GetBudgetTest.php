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

use App\Budget\Enum\BudgetPeriodType;
use App\Tests\Support\BudgetFunctionalTestCase;
use App\Wallet\Enum\CurrencyCode;
use Symfony\Component\HttpFoundation\Response;

final class GetBudgetTest extends BudgetFunctionalTestCase
{
    public function testAuthenticatedUserCanGetOwnBudget(): void
    {
        $user = $this->authenticateUser();

        $budget = $this->createBudget(
            user: $user,
            name: 'Budżet domowy',
            amount: 350000,
            currency: CurrencyCode::PLN,
            periodType: BudgetPeriodType::MONTHLY,
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/budgets/%d', $budgetId),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame($budgetId, $data['id']);
        self::assertSame('Budżet domowy', $data['name']);
        self::assertSame(350000, $data['amount']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame('monthly', $data['periodType']);
        self::assertSame('2026-06-01', $data['startDate']);
        self::assertSame('2026-06-30', $data['endDate']);
        self::assertArrayHasKey('createdAt', $data);
        self::assertArrayHasKey('updatedAt', $data);
    }

    public function testAuthenticatedUserCannotGetAnotherUserBudget(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $budget = $this->createBudget(
            user: $owner,
            name: 'Cudzy budżet',
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $this->authenticateUser(
            email: 'intruder@example.com',
            username: 'intruder',
        );

        $response = $this->getJson(
            sprintf('/api/budgets/%d', $budgetId),
        );

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
        );
    }

    public function testGetMissingBudgetReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/budgets/999999');

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
        );
    }

    public function testGetBudgetWithInvalidIdReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/budgets/abc');

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotGetBudget(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $budget = $this->createBudget(user: $owner);

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/budgets/%d', $budgetId),
        );

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
        );
    }
}