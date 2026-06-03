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

namespace App\Tests\Support;

use App\Budget\Entity\Budget;
use App\Budget\Enum\BudgetPeriodType;
use App\Budget\Repository\BudgetRepository;
use App\Entity\User;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

abstract class BudgetFunctionalTestCase extends FunctionalTestCase
{
    protected const DEFAULT_START_DATE = '2026-06-01';
    protected const DEFAULT_END_DATE = '2026-06-30';

    protected function authenticateUser(
        string $email = 'budget-owner@example.com',
        string $username = 'budget-owner',
    ): User {
        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->postJson('/api/pin/setup', [
            'pin' => '123456',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        /** @var User|null $user */
        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    protected function createBudget(
        User $user,
        string $name = 'Budżet domowy',
        int $amount = 300000,
        CurrencyCode $currency = CurrencyCode::PLN,
        BudgetPeriodType $periodType = BudgetPeriodType::MONTHLY,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null,
    ): Budget {
        /** @var Budget $budget */
        $budget = new Budget(
            user: $user,
            name: $name,
            amount: $amount,
            currency: $currency,
            periodType: $periodType,
            startDate: $startDate ?? new DateTimeImmutable(self::DEFAULT_START_DATE),
            endDate: $endDate ?? new DateTimeImmutable(self::DEFAULT_END_DATE),
        );

        $this->entityManager->persist($budget);
        $this->entityManager->flush();

        return $budget;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    protected function createBudgetThroughApi(array $overrides = []): array
    {
        $response = $this->postJson('/api/budgets', $overrides + [
                'name' => 'Budżet domowy',
                'amount' => 300000,
                'currency' => 'PLN',
                'periodType' => 'monthly',
                'startDate' => self::DEFAULT_START_DATE,
                'endDate' => self::DEFAULT_END_DATE,
            ]);

        self::assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertIsInt($data['id']);

        return $data;
    }

    protected function getJson(string $uri): Response
    {
        $this->client->request(
            method: 'GET',
            uri: $uri,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $this->clientIp,
            ],
        );

        return $this->client->getResponse();
    }

    protected function deleteJson(string $uri): Response
    {
        $this->client->request(
            method: 'DELETE',
            uri: $uri,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $this->clientIp,
            ],
        );

        return $this->client->getResponse();
    }

    protected function findBudget(int $id): ?Budget
    {
        return static::getContainer()
            ->get(BudgetRepository::class)
            ->find($id);
    }

    protected function findBudgetFresh(int $id): ?Budget
    {
        $this->entityManager->clear();

        return $this->findBudget($id);
    }

    /**
     * @return list<Budget>
     */
    protected function findBudgetsForUser(User $user): array
    {
        /** @var list<Budget> $budgets */
        $budgets = static::getContainer()
            ->get(BudgetRepository::class)
            ->findBy(['user' => $user]);

        return $budgets;
    }
}