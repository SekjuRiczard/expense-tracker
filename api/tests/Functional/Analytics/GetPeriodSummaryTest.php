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

final class GetPeriodSummaryTest extends AnalyticsFunctionalTestCase
{
    public function testAuthenticatedUserReceivesEmptySummary(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/analytics/summary'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame('PLN', $data['currency']);
        self::assertSame('2026-06-01', $data['from']);
        self::assertSame('2026-06-30', $data['to']);
        self::assertSame(0, $data['income']);
        self::assertSame(0, $data['expense']);
        self::assertSame(0, $data['balance']);
        self::assertSame(0, $data['transactionCount']);
    }

    public function testSummaryCalculatesIncomeExpenseBalanceAndTransactionCount(): void
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
            title: 'Wypłata',
            transactionDate: new DateTimeImmutable(
                '2026-06-05T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $expenseCategory,
            type: TransactionType::EXPENSE,
            amount: 120000,
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
            amount: 80000,
            title: 'Rachunki',
            transactionDate: new DateTimeImmutable(
                '2026-06-20T18:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/summary'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(700000, $data['income']);
        self::assertSame(200000, $data['expense']);
        self::assertSame(500000, $data['balance']);
        self::assertSame(3, $data['transactionCount']);
    }

    public function testSummaryIncludesTransactionsFromLastDayOfPeriod(): void
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
            '/api/analytics/summary'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['expense']);
        self::assertSame(-50000, $data['balance']);
        self::assertSame(1, $data['transactionCount']);
    }

    public function testSummaryIgnoresTransactionsOutsideSelectedPeriod(): void
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
                '2026-06-15T12:00:00+00:00',
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
            '/api/analytics/summary'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['expense']);
        self::assertSame(-50000, $data['balance']);
        self::assertSame(1, $data['transactionCount']);
    }

    public function testSummaryIgnoresTransactionsFromDifferentCurrency(): void
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
            '/api/analytics/summary'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['expense']);
        self::assertSame(-50000, $data['balance']);
        self::assertSame(1, $data['transactionCount']);
    }

    public function testSummaryIgnoresAnotherUserTransactions(): void
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
            '/api/analytics/summary'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['expense']);
        self::assertSame(-50000, $data['balance']);
        self::assertSame(1, $data['transactionCount']);
    }

    public function testSummaryWithInvalidDateRangeReturnsValidationError(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/analytics/summary'
            . '?from=2026-07-01'
            . '&to=2026-06-01'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotGetSummary(): void
    {
        $response = $this->getJson(
            '/api/analytics/summary'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
        );
    }
}