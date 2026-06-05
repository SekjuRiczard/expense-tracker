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

final class GetDashboardTest extends AnalyticsFunctionalTestCase
{
    public function testAuthenticatedUserReceivesEmptyDashboard(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/analytics/dashboard'
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
            [
                'currency' => 'PLN',
                'from' => '2026-01-01',
                'to' => '2026-12-31',
                'income' => 0,
                'expense' => 0,
                'balance' => 0,
                'transactionCount' => 0,
            ],
            $data['summary'],
        );

        self::assertSame([], $data['categoryBreakdown']);
        self::assertSame([], $data['cashFlow']);
    }

    public function testDashboardReturnsCompleteFinancialOverview(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(
            user: $user,
            currency: CurrencyCode::PLN,
        );

        $foodCategory = $this->createUserCategory(
            user: $user,
            name: 'Jedzenie',
            type: CategoryType::EXPENSE,
        );

        $transportCategory = $this->createUserCategory(
            user: $user,
            name: 'Transport',
            type: CategoryType::EXPENSE,
        );

        $incomeCategory = $this->createUserCategory(
            user: $user,
            name: 'Wynagrodzenie',
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
            category: $foodCategory,
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
            category: $transportCategory,
            type: TransactionType::EXPENSE,
            amount: 80000,
            title: 'Paliwo',
            transactionDate: new DateTimeImmutable(
                '2026-06-15T12:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/dashboard'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame('PLN', $data['summary']['currency']);
        self::assertSame('2026-06-01', $data['summary']['from']);
        self::assertSame('2026-06-30', $data['summary']['to']);
        self::assertSame(700000, $data['summary']['income']);
        self::assertSame(200000, $data['summary']['expense']);
        self::assertSame(500000, $data['summary']['balance']);
        self::assertSame(3, $data['summary']['transactionCount']);

        self::assertCount(2, $data['categoryBreakdown']);

        self::assertSame(
            'Jedzenie',
            $data['categoryBreakdown'][0]['categoryName'],
        );
        self::assertSame(
            120000,
            $data['categoryBreakdown'][0]['amount'],
        );
        self::assertEquals(
            60.0,
            $data['categoryBreakdown'][0]['percentage'],
        );

        self::assertSame(
            'Transport',
            $data['categoryBreakdown'][1]['categoryName'],
        );
        self::assertSame(
            80000,
            $data['categoryBreakdown'][1]['amount'],
        );
        self::assertEquals(
            40.0,
            $data['categoryBreakdown'][1]['percentage'],
        );

        self::assertCount(1, $data['cashFlow']);

        self::assertSame('2026-06', $data['cashFlow'][0]['period']);
        self::assertSame(700000, $data['cashFlow'][0]['income']);
        self::assertSame(200000, $data['cashFlow'][0]['expense']);
        self::assertSame(500000, $data['cashFlow'][0]['balance']);
    }

    public function testDashboardReturnsCashFlowForMultipleMonths(): void
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
            title: 'Wypłata — styczeń',
            transactionDate: new DateTimeImmutable(
                '2026-01-05T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $expenseCategory,
            type: TransactionType::EXPENSE,
            amount: 150000,
            title: 'Wydatki — styczeń',
            transactionDate: new DateTimeImmutable(
                '2026-01-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $incomeCategory,
            type: TransactionType::INCOME,
            amount: 650000,
            title: 'Wypłata — luty',
            transactionDate: new DateTimeImmutable(
                '2026-02-05T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $expenseCategory,
            type: TransactionType::EXPENSE,
            amount: 200000,
            title: 'Wydatki — luty',
            transactionDate: new DateTimeImmutable(
                '2026-02-10T12:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/dashboard'
            . '?from=2026-01-01'
            . '&to=2026-02-28'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(1250000, $data['summary']['income']);
        self::assertSame(350000, $data['summary']['expense']);
        self::assertSame(900000, $data['summary']['balance']);
        self::assertSame(4, $data['summary']['transactionCount']);

        self::assertCount(2, $data['cashFlow']);

        self::assertSame('2026-01', $data['cashFlow'][0]['period']);
        self::assertSame(600000, $data['cashFlow'][0]['income']);
        self::assertSame(150000, $data['cashFlow'][0]['expense']);
        self::assertSame(450000, $data['cashFlow'][0]['balance']);

        self::assertSame('2026-02', $data['cashFlow'][1]['period']);
        self::assertSame(650000, $data['cashFlow'][1]['income']);
        self::assertSame(200000, $data['cashFlow'][1]['expense']);
        self::assertSame(450000, $data['cashFlow'][1]['balance']);
    }

    public function testDashboardIncludesTransactionFromLastDayOfPeriod(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            name: 'Jedzenie',
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
            '/api/analytics/dashboard'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['summary']['expense']);
        self::assertSame(-50000, $data['summary']['balance']);
        self::assertSame(1, $data['summary']['transactionCount']);

        self::assertCount(1, $data['categoryBreakdown']);
        self::assertSame(
            50000,
            $data['categoryBreakdown'][0]['amount'],
        );

        self::assertCount(1, $data['cashFlow']);
        self::assertSame('2026-06', $data['cashFlow'][0]['period']);
        self::assertSame(50000, $data['cashFlow'][0]['expense']);
    }

    public function testDashboardIgnoresTransactionsOutsideSelectedPeriod(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            name: 'Jedzenie',
            type: CategoryType::EXPENSE,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 50000,
            title: 'Czerwcowy wydatek',
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
            title: 'Lipcowy wydatek',
            transactionDate: new DateTimeImmutable(
                '2026-07-01T00:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/dashboard'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['summary']['expense']);
        self::assertSame(1, $data['summary']['transactionCount']);

        self::assertCount(1, $data['categoryBreakdown']);
        self::assertSame(
            50000,
            $data['categoryBreakdown'][0]['amount'],
        );

        self::assertCount(1, $data['cashFlow']);
        self::assertSame(50000, $data['cashFlow'][0]['expense']);
    }

    public function testDashboardIgnoresTransactionsFromDifferentCurrency(): void
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
            name: 'Jedzenie',
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
            '/api/analytics/dashboard'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['summary']['expense']);
        self::assertSame(1, $data['summary']['transactionCount']);

        self::assertCount(1, $data['categoryBreakdown']);
        self::assertSame(
            50000,
            $data['categoryBreakdown'][0]['amount'],
        );

        self::assertCount(1, $data['cashFlow']);
        self::assertSame(50000, $data['cashFlow'][0]['expense']);
    }

    public function testDashboardIgnoresAnotherUserTransactions(): void
    {
        $user = $this->authenticateUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $wallet = $this->createWallet(user: $user);

        $category = $this->createUserCategory(
            user: $user,
            name: 'Jedzenie',
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
            name: 'Cudza kategoria',
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
            '/api/analytics/dashboard'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(50000, $data['summary']['expense']);
        self::assertSame(1, $data['summary']['transactionCount']);

        self::assertCount(1, $data['categoryBreakdown']);
        self::assertSame(
            'Jedzenie',
            $data['categoryBreakdown'][0]['categoryName'],
        );
        self::assertSame(
            50000,
            $data['categoryBreakdown'][0]['amount'],
        );

        self::assertCount(1, $data['cashFlow']);
        self::assertSame(50000, $data['cashFlow'][0]['expense']);
    }

    public function testDashboardWithInvalidDateRangeReturnsValidationError(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/analytics/dashboard'
            . '?from=2026-07-01'
            . '&to=2026-06-01'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotGetDashboard(): void
    {
        $response = $this->getJson(
            '/api/analytics/dashboard'
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