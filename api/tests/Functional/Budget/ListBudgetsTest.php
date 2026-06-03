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
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class ListBudgetsTest extends BudgetFunctionalTestCase
{
    public function testAuthenticatedUserListsOnlyOwnBudgets(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $this->createBudget(
            user: $owner,
            name: 'Cudzy budżet',
        );

        $user = $this->authenticateUser(
            email: 'viewer@example.com',
            username: 'viewer',
        );

        $this->createBudget(
            user: $user,
            name: 'Mój budżet',
        );

        $response = $this->getJson('/api/budgets');

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(1, $data);
        self::assertSame('Mój budżet', $data[0]['name']);
    }

    public function testAuthenticatedUserWithoutBudgetsReceivesEmptyList(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/budgets');

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        self::assertSame([], $this->jsonResponse());
    }

    public function testBudgetsAreSortedByStartDateDesc(): void
    {
        $user = $this->authenticateUser();

        $this->createBudget(
            user: $user,
            name: 'Najstarszy',
            periodType: BudgetPeriodType::CUSTOM,
            startDate: new DateTimeImmutable('2026-01-01'),
            endDate: new DateTimeImmutable('2026-01-31'),
        );

        $this->createBudget(
            user: $user,
            name: 'Najnowszy',
            periodType: BudgetPeriodType::CUSTOM,
            startDate: new DateTimeImmutable('2026-12-01'),
            endDate: new DateTimeImmutable('2026-12-31'),
        );

        $this->createBudget(
            user: $user,
            name: 'Środkowy',
            periodType: BudgetPeriodType::CUSTOM,
            startDate: new DateTimeImmutable('2026-06-01'),
            endDate: new DateTimeImmutable('2026-06-30'),
        );

        $response = $this->getJson('/api/budgets');

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(
            ['Najnowszy', 'Środkowy', 'Najstarszy'],
            array_column($data, 'name'),
        );
    }

    public function testGuestCannotListBudgets(): void
    {
        $response = $this->getJson('/api/budgets');

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
        );
    }
}