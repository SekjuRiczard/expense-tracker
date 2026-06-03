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

use App\Budget\Entity\Budget;
use App\Budget\Enum\BudgetPeriodType;
use App\Tests\Support\BudgetFunctionalTestCase;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class UpdateBudgetTest extends BudgetFunctionalTestCase
{
    public function testAuthenticatedUserCanUpdateBudgetName(): void
    {
        $this->authenticateUser();

        $budget = $this->createBudgetThroughApi([
            'name' => 'Stara nazwa',
        ]);

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budget['id']),
            [
                'name' => 'Nowa nazwa',
            ],
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame('Nowa nazwa', $data['name']);
        self::assertSame(300000, $data['amount']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame('monthly', $data['periodType']);

        $updatedBudget = $this->findBudgetFresh($budget['id']);

        self::assertInstanceOf(Budget::class, $updatedBudget);
        self::assertSame('Nowa nazwa', $updatedBudget->getName());
    }

    public function testAuthenticatedUserCanUpdateOnlyBudgetAmount(): void
    {
        $this->authenticateUser();

        $budget = $this->createBudgetThroughApi([
            'amount' => 300000,
        ]);

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budget['id']),
            [
                'amount' => 500000,
            ],
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame('Budżet domowy', $data['name']);
        self::assertSame(500000, $data['amount']);

        $updatedBudget = $this->findBudgetFresh($budget['id']);

        self::assertInstanceOf(Budget::class, $updatedBudget);
        self::assertSame(500000, $updatedBudget->getAmount());
    }

    public function testAuthenticatedUserCanChangeBudgetToCustomPeriod(): void
    {
        $this->authenticateUser();

        $budget = $this->createBudgetThroughApi();

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budget['id']),
            [
                'periodType' => 'custom',
                'startDate' => '2026-07-10',
                'endDate' => '2026-08-18',
            ],
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame('custom', $data['periodType']);
        self::assertSame('2026-07-10', $data['startDate']);
        self::assertSame('2026-08-18', $data['endDate']);

        $updatedBudget = $this->findBudgetFresh($budget['id']);

        self::assertInstanceOf(Budget::class, $updatedBudget);
        self::assertSame(
            BudgetPeriodType::CUSTOM,
            $updatedBudget->getPeriodType(),
        );
        self::assertSame(
            '2026-07-10',
            $updatedBudget->getStartDate()->format('Y-m-d'),
        );
        self::assertSame(
            '2026-08-18',
            $updatedBudget->getEndDate()->format('Y-m-d'),
        );
    }

    public function testAuthenticatedUserCanUpdateBudgetCurrency(): void
    {
        $this->authenticateUser();

        $budget = $this->createBudgetThroughApi([
            'currency' => 'PLN',
        ]);

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budget['id']),
            [
                'currency' => 'EUR',
            ],
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $updatedBudget = $this->findBudgetFresh($budget['id']);

        self::assertInstanceOf(Budget::class, $updatedBudget);
        self::assertSame(
            CurrencyCode::EUR,
            $updatedBudget->getCurrency(),
        );
    }

    public function testUpdateBudgetWithEmptyPayloadReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $budget = $this->createBudgetThroughApi();

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budget['id']),
            [],
        );

        self::assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
        );
    }

    public function testCannotUpdateBudgetWithBlankName(): void
    {
        $this->authenticateUser();

        $budget = $this->createBudgetThroughApi();

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budget['id']),
            [
                'name' => '',
            ],
        );

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );

        $unchangedBudget = $this->findBudgetFresh($budget['id']);

        self::assertInstanceOf(Budget::class, $unchangedBudget);
        self::assertSame('Budżet domowy', $unchangedBudget->getName());
    }

    public function testCannotUpdateMonthlyBudgetToPartialMonth(): void
    {
        $this->authenticateUser();

        $budget = $this->createBudgetThroughApi();

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budget['id']),
            [
                'startDate' => '2026-06-05',
            ],
        );

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );

        $unchangedBudget = $this->findBudgetFresh($budget['id']);

        self::assertInstanceOf(Budget::class, $unchangedBudget);
        self::assertSame(
            '2026-06-01',
            $unchangedBudget->getStartDate()->format('Y-m-d'),
        );
    }

    public function testCannotUpdateBudgetWithInvalidDateRange(): void
    {
        $this->authenticateUser();

        $budget = $this->createBudgetThroughApi();

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budget['id']),
            [
                'periodType' => 'custom',
                'startDate' => '2026-09-01',
                'endDate' => '2026-08-01',
            ],
        );

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testCannotUpdateBudgetToDuplicatePeriod(): void
    {
        $user = $this->authenticateUser();

        $juneBudget = $this->createBudget(
            user: $user,
            name: 'Czerwiec',
            startDate: new DateTimeImmutable('2026-06-01'),
            endDate: new DateTimeImmutable('2026-06-30'),
        );

        $julyBudget = $this->createBudget(
            user: $user,
            name: 'Lipiec',
            startDate: new DateTimeImmutable('2026-07-01'),
            endDate: new DateTimeImmutable('2026-07-31'),
        );

        $juneBudgetId = $juneBudget->getId();
        $julyBudgetId = $julyBudget->getId();

        self::assertIsInt($juneBudgetId);
        self::assertIsInt($julyBudgetId);

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $julyBudgetId),
            [
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        );

        self::assertSame(
            Response::HTTP_CONFLICT,
            $response->getStatusCode(),
        );

        $unchangedBudget = $this->findBudgetFresh($julyBudgetId);

        self::assertInstanceOf(Budget::class, $unchangedBudget);
        self::assertSame(
            '2026-07-01',
            $unchangedBudget->getStartDate()->format('Y-m-d'),
        );
        self::assertSame(
            '2026-07-31',
            $unchangedBudget->getEndDate()->format('Y-m-d'),
        );
    }

    public function testAuthenticatedUserCannotUpdateAnotherUserBudget(): void
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

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budgetId),
            [
                'name' => 'Przejęty budżet',
            ],
        );

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
        );

        $unchangedBudget = $this->findBudgetFresh($budgetId);

        self::assertInstanceOf(Budget::class, $unchangedBudget);
        self::assertSame('Cudzy budżet', $unchangedBudget->getName());
    }

    public function testUpdateMissingBudgetReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->patchJson(
            '/api/budgets/999999',
            [
                'name' => 'Nowa nazwa',
            ],
        );

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotUpdateBudget(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $budget = $this->createBudget(user: $owner);

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->patchJson(
            sprintf('/api/budgets/%d', $budgetId),
            [
                'amount' => 500000,
            ],
        );

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
        );
    }

    public function testUpdateBudgetWithMalformedJsonReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $budget = $this->createBudgetThroughApi();

        $response = $this->patchMalformedJson(
            sprintf('/api/budgets/%d', $budget['id']),
            '{"name": "Nowa nazwa"',
        );

        self::assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
        );
    }
}