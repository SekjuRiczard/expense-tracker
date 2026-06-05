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

final class GetCashFlowTest extends AnalyticsFunctionalTestCase
{
    public function testAuthenticatedUserReceivesEmptyCashFlow(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-01-01'
            . '&to=2026-12-31'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        self::assertSame([], $this->jsonResponse());
    }

    public function testCashFlowGroupsTransactionsByMonth(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(
            user: $user,
            currency: CurrencyCode::PLN,
        );

        $expenseCategory = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $incomeCategory = $this->createUserCategory(
            user: $user,
            type: CategoryType::INCOME,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $incomeCategory,
            type: TransactionType::INCOME,
            amount: 700000,
            title: 'Wypłata — styczeń',
            transactionDate: new DateTimeImmutable(
                '2026-01-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $expenseCategory,
            type: TransactionType::EXPENSE,
            amount: 180000,
            title: 'Wydatki — styczeń',
            transactionDate: new DateTimeImmutable(
                '2026-01-15T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $incomeCategory,
            type: TransactionType::INCOME,
            amount: 720000,
            title: 'Wypłata — luty',
            transactionDate: new DateTimeImmutable(
                '2026-02-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $expenseCategory,
            type: TransactionType::EXPENSE,
            amount: 220000,
            title: 'Wydatki — luty',
            transactionDate: new DateTimeImmutable(
                '2026-02-15T12:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-01-01'
            . '&to=2026-12-31'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(2, $data);

        self::assertSame('2026-01', $data[0]['period']);
        self::assertSame(700000, $data[0]['income']);
        self::assertSame(180000, $data[0]['expense']);
        self::assertSame(520000, $data[0]['balance']);

        self::assertSame('2026-02', $data[1]['period']);
        self::assertSame(720000, $data[1]['income']);
        self::assertSame(220000, $data[1]['expense']);
        self::assertSame(500000, $data[1]['balance']);
    }

    public function testCashFlowSumsMultipleTransactionsFromTheSameMonth(): void
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

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $incomeCategory,
            type: TransactionType::INCOME,
            amount: 600000,
            title: 'Wypłata',
            transactionDate: new DateTimeImmutable(
                '2026-06-01T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $incomeCategory,
            type: TransactionType::INCOME,
            amount: 100000,
            title: 'Premia',
            transactionDate: new DateTimeImmutable(
                '2026-06-05T12:00:00+00:00',
            ),
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
            category: $expenseCategory,
            type: TransactionType::EXPENSE,
            amount: 30000,
            title: 'Transport',
            transactionDate: new DateTimeImmutable(
                '2026-06-15T12:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(1, $data);
        self::assertSame('2026-06', $data[0]['period']);
        self::assertSame(700000, $data[0]['income']);
        self::assertSame(80000, $data[0]['expense']);
        self::assertSame(620000, $data[0]['balance']);
    }

    public function testCashFlowReturnsNegativeBalanceWhenExpensesExceedIncome(): void
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

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $incomeCategory,
            type: TransactionType::INCOME,
            amount: 100000,
            title: 'Mały przychód',
            transactionDate: new DateTimeImmutable(
                '2026-06-05T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $expenseCategory,
            type: TransactionType::EXPENSE,
            amount: 160000,
            title: 'Duży wydatek',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(1, $data);
        self::assertSame(-60000, $data[0]['balance']);
    }

    public function testCashFlowSortsPeriodsChronologically(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 30000,
            title: 'Marzec',
            transactionDate: new DateTimeImmutable(
                '2026-03-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 10000,
            title: 'Styczeń',
            transactionDate: new DateTimeImmutable(
                '2026-01-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 20000,
            title: 'Luty',
            transactionDate: new DateTimeImmutable(
                '2026-02-10T12:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-01-01'
            . '&to=2026-12-31'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(
            ['2026-01', '2026-02', '2026-03'],
            array_column($data, 'period'),
        );
    }

    public function testCashFlowSeparatesMonthsFromDifferentYears(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 50000,
            title: 'Grudzień 2025',
            transactionDate: new DateTimeImmutable(
                '2025-12-15T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 70000,
            title: 'Styczeń 2026',
            transactionDate: new DateTimeImmutable(
                '2026-01-15T12:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2025-12-01'
            . '&to=2026-01-31'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(2, $data);
        self::assertSame('2025-12', $data[0]['period']);
        self::assertSame(50000, $data[0]['expense']);
        self::assertSame('2026-01', $data[1]['period']);
        self::assertSame(70000, $data[1]['expense']);
    }

    public function testCashFlowIncludesTransactionFromLastDayOfPeriod(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
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

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(1, $data);
        self::assertSame('2026-06', $data[0]['period']);
        self::assertSame(50000, $data[0]['expense']);
    }

    public function testCashFlowIgnoresTransactionsOutsideSelectedPeriod(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
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

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(1, $data);
        self::assertSame('2026-06', $data[0]['period']);
        self::assertSame(50000, $data[0]['expense']);
    }

    public function testCashFlowIgnoresTransactionsFromDifferentCurrency(): void
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

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(1, $data);
        self::assertSame(50000, $data[0]['expense']);
    }

    public function testCashFlowIgnoresAnotherUserTransactions(): void
    {
        $user = $this->authenticateUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            type: CategoryType::EXPENSE,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 50000,
            title: 'Mój wydatek',
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

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(1, $data);
        self::assertSame(50000, $data[0]['expense']);
    }

    public function testCashFlowWithInvalidDateRangeReturnsValidationError(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-07-01'
            . '&to=2026-06-01'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotGetCashFlow(): void
    {
        $response = $this->getJson(
            '/api/analytics/cash-flow'
            . '?from=2026-01-01'
            . '&to=2026-12-31'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
        );
    }
}