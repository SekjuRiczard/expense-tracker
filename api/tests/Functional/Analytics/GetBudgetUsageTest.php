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

namespace App\Tests\Functional\Analytics;

use App\Category\Enum\CategoryType;
use App\Tests\Support\AnalyticsFunctionalTestCase;
use App\Transaction\Enum\TransactionType;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class GetBudgetUsageTest extends AnalyticsFunctionalTestCase
{
    public function testAuthenticatedUserReceivesEmptyBudgetUsage(): void
    {
        $user = $this->authenticateUser();

        $budget = $this->createBudget(
            user: $user,
            amount: 300000,
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame($budgetId, $data['budgetId']);
        self::assertSame('Budżet domowy', $data['budgetName']);
        self::assertSame(300000, $data['budgetAmount']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame('2026-06-01', $data['startDate']);
        self::assertSame('2026-06-30', $data['endDate']);
        self::assertSame(0, $data['spent']);
        self::assertSame(300000, $data['remaining']);
        self::assertSame(0, $data['percentage']);
        self::assertFalse($data['exceeded']);
    }

    public function testUsageSumsExpensesFromBudgetPeriod(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(
            user: $user,
            currency: CurrencyCode::PLN,
        );

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $budget = $this->createBudget(
            user: $user,
            amount: 300000,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 100000,
            title: 'Zakupy',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 85000,
            title: 'Rachunki',
            transactionDate: new DateTimeImmutable(
                '2026-06-20T18:00:00+00:00',
            ),
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(185000, $data['spent']);
        self::assertSame(115000, $data['remaining']);
        self::assertSame(61.67, $data['percentage']);
        self::assertFalse($data['exceeded']);
    }

    public function testUsageIncludesExpenseFromLastDayOfBudgetPeriod(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $budget = $this->createBudget(
            user: $user,
            amount: 300000,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 50000,
            title: 'Ostatni dzień miesiąca',
            transactionDate: new DateTimeImmutable(
                '2026-06-30T23:59:59+00:00',
            ),
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['spent']);
    }

    public function testUsageIgnoresIncomeTransactions(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $expenseCategory = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $incomeCategory = $this->createUserCategory(
            user: $user,
            type: CategoryType::INCOME,
        );

        $budget = $this->createBudget(
            user: $user,
            amount: 300000,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $expenseCategory,
            type: TransactionType::EXPENSE,
            amount: 50000,
            title: 'Zakupy',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $incomeCategory,
            type: TransactionType::INCOME,
            amount: 700000,
            title: 'Wypłata',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['spent']);
    }

    public function testUsageIgnoresExpensesOutsideBudgetPeriod(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $budget = $this->createBudget(
            user: $user,
            amount: 300000,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 50000,
            title: 'Czerwcowa',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 90000,
            title: 'Lipcowa',
            transactionDate: new DateTimeImmutable(
                '2026-07-01T00:00:00+00:00',
            ),
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['spent']);
    }

    public function testUsageIgnoresExpensesFromDifferentCurrency(): void
    {
        $user = $this->authenticateUser();

        $plnWallet = $this->createWallet(
            user: $user,
            name: 'Portfel PLN',
            currency: CurrencyCode::PLN,
        );

        $eurWallet = $this->createWallet(
            user: $user,
            name: 'Portfel EUR',
            currency: CurrencyCode::EUR,
        );

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $budget = $this->createBudget(
            user: $user,
            amount: 300000,
            currency: CurrencyCode::PLN,
        );

        $this->createTransaction(
            user: $user,
            wallet: $plnWallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 50000,
            title: 'Wydatek PLN',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $eurWallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 90000,
            title: 'Wydatek EUR',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['spent']);
    }

    public function testUsageMarksExceededBudget(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $budget = $this->createBudget(
            user: $user,
            amount: 100000,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 125000,
            title: 'Przekroczenie budżetu',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(125000, $data['spent']);
        self::assertSame(-25000, $data['remaining']);
        self::assertSame(125, $data['percentage']);
        self::assertTrue($data['exceeded']);
    }

    public function testUsageIgnoresAnotherUserTransactions(): void
    {
        $owner = $this->authenticateUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $ownerWallet = $this->createWallet(user: $owner);

        $ownerCategory = $this->createUserCategory(
            user: $owner,
            type: CategoryType::EXPENSE,
        );

        $budget = $this->createBudget(
            user: $owner,
            amount: 300000,
        );

        $this->createTransaction(
            user: $owner,
            wallet: $ownerWallet,
            category: $ownerCategory,
            type: TransactionType::EXPENSE,
            amount: 50000,
            title: 'Wydatek właściciela',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $anotherUser = $this->createUser(
            email: 'another@example.com',
            username: 'another',
        );

        $anotherWallet = $this->createWallet(user: $anotherUser);

        $anotherCategory = $this->createUserCategory(
            user: $anotherUser,
            type: CategoryType::EXPENSE,
        );

        $this->createTransaction(
            user: $anotherUser,
            wallet: $anotherWallet,
            category: $anotherCategory,
            type: TransactionType::EXPENSE,
            amount: 90000,
            title: 'Cudzy wydatek',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['spent']);
    }

    public function testAuthenticatedUserCannotGetAnotherUserBudgetUsage(): void
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

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
        );
    }

    public function testMissingBudgetUsageReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/analytics/budgets/999999/usage',
        );

        self::assertSame(
            Response::HTTP_NOT_FOUND,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotGetBudgetUsage(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $budget = $this->createBudget(user: $owner);

        $budgetId = $budget->getId();

        self::assertIsInt($budgetId);

        $response = $this->getJson(
            sprintf('/api/analytics/budgets/%d/usage', $budgetId),
        );

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
        );
    }
}