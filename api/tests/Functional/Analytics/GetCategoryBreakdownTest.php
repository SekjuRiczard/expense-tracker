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

final class GetCategoryBreakdownTest extends AnalyticsFunctionalTestCase
{
    public function testAuthenticatedUserReceivesEmptyCategoryBreakdown(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/analytics/categories'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        self::assertSame([], $this->jsonResponse());
    }

    public function testCategoryBreakdownGroupsExpensesByCategory(): void
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

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $foodCategory,
            type: TransactionType::EXPENSE,
            amount: 50000,
            title: 'Zakupy spożywcze',
            transactionDate: new DateTimeImmutable(
                '2026-06-05T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $foodCategory,
            type: TransactionType::EXPENSE,
            amount: 35000,
            title: 'Restauracja',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T18:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $transportCategory,
            type: TransactionType::EXPENSE,
            amount: 35000,
            title: 'Paliwo',
            transactionDate: new DateTimeImmutable(
                '2026-06-15T08:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/categories'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertCount(2, $data);

        self::assertSame('Jedzenie', $data[0]['categoryName']);
        self::assertSame(85000, $data[0]['amount']);
        self::assertSame(70.83, $data[0]['percentage']);

        self::assertSame('Transport', $data[1]['categoryName']);
        self::assertSame(35000, $data[1]['amount']);
        self::assertSame(29.17, $data[1]['percentage']);
    }

    public function testCategoryBreakdownSortsCategoriesByAmountDesc(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $smallCategory = $this->createUserCategory(
            user: $user,
            name: 'Mała kategoria',
            type: CategoryType::EXPENSE,
        );

        $largeCategory = $this->createUserCategory(
            user: $user,
            name: 'Duża kategoria',
            type: CategoryType::EXPENSE,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $smallCategory,
            type: TransactionType::EXPENSE,
            amount: 10000,
            title: 'Mały wydatek',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $largeCategory,
            type: TransactionType::EXPENSE,
            amount: 90000,
            title: 'Duży wydatek',
            transactionDate: new DateTimeImmutable(
                '2026-06-10T12:00:00+00:00',
            ),
        );

        $response = $this->getJson(
            '/api/analytics/categories'
            . '?from=2026-06-01'
            . '&to=2026-06-30'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame(
            ['Duża kategoria', 'Mała kategoria'],
            array_column($data, 'categoryName'),
        );
    }

    public function testCategoryBreakdownIgnoresIncomeTransactions(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);

        $expenseCategory = $this->createUserCategory(
            user: $user,
            name: 'Jedzenie',
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

        $response = $this->getJson(
            '/api/analytics/categories'
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
        self::assertSame('Jedzenie', $data[0]['categoryName']);
        self::assertSame(50000, $data[0]['amount']);
        self::assertSame(100, $data[0]['percentage']);
    }

    public function testCategoryBreakdownIncludesExpenseFromLastDayOfPeriod(): void
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
            '/api/analytics/categories'
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
        self::assertSame(50000, $data[0]['amount']);
    }

    public function testCategoryBreakdownIgnoresExpensesOutsideSelectedPeriod(): void
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
            '/api/analytics/categories'
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
        self::assertSame(50000, $data[0]['amount']);
        self::assertSame(100, $data[0]['percentage']);
    }

    public function testCategoryBreakdownIgnoresExpensesFromDifferentCurrency(): void
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
            '/api/analytics/categories'
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
        self::assertSame(50000, $data[0]['amount']);
    }

    public function testCategoryBreakdownIgnoresAnotherUserExpenses(): void
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
            '/api/analytics/categories'
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
        self::assertSame('Jedzenie', $data[0]['categoryName']);
        self::assertSame(50000, $data[0]['amount']);
    }

    public function testCategoryBreakdownWithInvalidDateRangeReturnsValidationError(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/analytics/categories'
            . '?from=2026-07-01'
            . '&to=2026-06-01'
            . '&currency=PLN',
        );

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotGetCategoryBreakdown(): void
    {
        $response = $this->getJson(
            '/api/analytics/categories'
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