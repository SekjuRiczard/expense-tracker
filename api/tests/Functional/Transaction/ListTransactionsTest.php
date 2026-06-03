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

namespace App\Tests\Functional\Transaction;

use App\Category\Enum\CategoryType;
use App\Tests\Support\TransactionFunctionalTestCase;
use App\Transaction\Entity\Transaction;
use App\Transaction\Enum\TransactionType;
use Symfony\Component\HttpFoundation\Response;

final class ListTransactionsTest extends TransactionFunctionalTestCase
{
    public function testAuthenticatedUserListsOnlyOwnTransactions(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $foreignWallet = $this->createWallet(user: $owner);
        $foreignCategory = $this->createUserCategory(
            user: $owner,
            name: 'Cudza',
        );

        $this->createTransaction(
            user: $owner,
            wallet: $foreignWallet,
            category: $foreignCategory,
            title: 'Cudza transakcja',
        );

        $user = $this->authenticateUser(
            email: 'viewer@example.com',
            username: 'viewer',
        );

        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            title: 'Moja transakcja',
        );

        $response = $this->getJson('/api/transactions');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        /** @var list<array<string, mixed>> $items */
        $items = $data['items'];

        $titles = array_column($items, 'title');

        self::assertCount(1, $items);
        self::assertContains('Moja transakcja', $titles);
        self::assertNotContains('Cudza transakcja', $titles);

        self::assertSame(
            [
                'page' => 1,
                'limit' => 20,
                'totalItems' => 1,
                'totalPages' => 1,
            ],
            $data['pagination'],
        );
    }

    public function testAuthenticatedUserWithoutTransactionsReceivesEmptyList(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/transactions');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        self::assertSame(
            [
                'items' => [],
                'pagination' => [
                    'page' => 1,
                    'limit' => 20,
                    'totalItems' => 0,
                    'totalPages' => 0,
                ],
            ],
            $this->jsonResponse(),
        );
    }

    public function testTransactionsAreSortedByTransactionDateThenCreatedAtDesc(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            title: 'Najstarsza',
            transactionDate: new \DateTimeImmutable('2024-01-01T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            title: 'Najnowsza',
            transactionDate: new \DateTimeImmutable('2024-12-31T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            title: 'Środkowa',
            transactionDate: new \DateTimeImmutable('2024-06-15T10:00:00+00:00'),
        );

        $response = $this->getJson('/api/transactions');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(
            ['Najnowsza', 'Środkowa', 'Najstarsza'],
            array_column($data['items'], 'title'),
        );
    }

    public function testAuthenticatedUserCanPaginateTransactions(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            title: 'Najnowsza',
            transactionDate: new \DateTimeImmutable('2024-03-01T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            title: 'Środkowa',
            transactionDate: new \DateTimeImmutable('2024-02-01T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            title: 'Najstarsza',
            transactionDate: new \DateTimeImmutable('2024-01-01T10:00:00+00:00'),
        );

        $response = $this->getJson('/api/transactions?page=2&limit=2');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(
            ['Najstarsza'],
            array_column($data['items'], 'title'),
        );

        self::assertSame(
            [
                'page' => 2,
                'limit' => 2,
                'totalItems' => 3,
                'totalPages' => 2,
            ],
            $data['pagination'],
        );
    }

    public function testCannotListTransactionsWithInvalidPage(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/transactions?page=0');

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testCannotListTransactionsWithTooHighLimit(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/transactions?limit=101');

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotListTransactions(): void
    {
        $response = $this->getJson('/api/transactions');

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
        );
    }

    public function testAuthenticatedUserCanFilterTransactionsByType(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);

        $expenseCategory = $this->createUserCategory(
            user: $user,
            name: 'Wydatki',
            type: CategoryType::EXPENSE,
        );

        $incomeCategory = $this->createUserCategory(
            user: $user,
            name: 'Przychody',
            type: CategoryType::INCOME,
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $expenseCategory,
            type: TransactionType::EXPENSE,
            title: 'Zakupy',
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $incomeCategory,
            type: TransactionType::INCOME,
            title: 'Wypłata',
        );

        $response = $this->getJson('/api/transactions?type=expense');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(
            ['Zakupy'],
            array_column($data['items'], 'title'),
        );

        self::assertSame(1, $data['pagination']['totalItems']);
    }

    public function testCannotFilterTransactionsByInvalidType(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/transactions?type=invalid');

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testAuthenticatedUserCanFilterTransactionsByWallet(): void
    {
        $user = $this->authenticateUser();

        $walletA = $this->createWallet(
            user: $user,
            name: 'Gotówka',
        );

        $walletB = $this->createWallet(
            user: $user,
            name: 'Konto bankowe',
        );

        $category = $this->createUserCategory(user: $user);

        $this->createTransaction(
            user: $user,
            wallet: $walletA,
            category: $category,
            title: 'Zakupy gotówkowe',
        );

        $this->createTransaction(
            user: $user,
            wallet: $walletB,
            category: $category,
            title: 'Zakupy kartą',
        );

        $response = $this->getJson(
            sprintf('/api/transactions?walletId=%d', $walletA->getId()),
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(
            ['Zakupy gotówkowe'],
            array_column($data['items'], 'title'),
        );

        self::assertSame(1, $data['pagination']['totalItems']);
    }

    public function testCannotFilterTransactionsByInvalidWalletId(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/transactions?walletId=0');

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testAuthenticatedUserCanFilterTransactionsByCategory(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);

        $foodCategory = $this->createUserCategory(
            user: $user,
            name: 'Jedzenie',
        );

        $transportCategory = $this->createUserCategory(
            user: $user,
            name: 'Transport',
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $foodCategory,
            title: 'Zakupy spożywcze',
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $transportCategory,
            title: 'Bilet miesięczny',
        );

        $response = $this->getJson(
            sprintf('/api/transactions?categoryId=%d', $foodCategory->getId()),
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(
            ['Zakupy spożywcze'],
            array_column($data['items'], 'title'),
        );

        self::assertSame(1, $data['pagination']['totalItems']);
    }

    public function testCannotFilterTransactionsByInvalidCategoryId(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/transactions?categoryId=0');

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testAuthenticatedUserCanFilterTransactionsByDateRange(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            title: 'Styczniowa',
            transactionDate: new \DateTimeImmutable('2024-01-15T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            title: 'Lutowa',
            transactionDate: new \DateTimeImmutable('2024-02-15T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            title: 'Marcowa',
            transactionDate: new \DateTimeImmutable('2024-03-15T10:00:00+00:00'),
        );

        $response = $this->getJson(
            '/api/transactions?from=2024-02-01&to=2024-02-29T23:59:59',
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(
            ['Lutowa'],
            array_column($data['items'], 'title'),
        );

        self::assertSame(1, $data['pagination']['totalItems']);
    }

    public function testCannotListTransactionsWithInvalidDateRange(): void
    {
        $this->authenticateUser();

        $response = $this->getJson(
            '/api/transactions?from=2024-12-31&to=2024-01-01',
        );

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testAuthenticatedUserCanCombineTransactionFilters(): void
    {
        $user = $this->authenticateUser();

        $walletA = $this->createWallet(
            user: $user,
            name: 'Gotówka',
        );

        $walletB = $this->createWallet(
            user: $user,
            name: 'Konto bankowe',
        );

        $foodCategory = $this->createUserCategory(
            user: $user,
            name: 'Jedzenie',
        );

        $transportCategory = $this->createUserCategory(
            user: $user,
            name: 'Transport',
        );

        $this->createTransaction(
            user: $user,
            wallet: $walletA,
            category: $foodCategory,
            type: TransactionType::EXPENSE,
            title: 'Pasująca',
            transactionDate: new \DateTimeImmutable('2024-02-15T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $walletB,
            category: $foodCategory,
            type: TransactionType::EXPENSE,
            title: 'Inny portfel',
            transactionDate: new \DateTimeImmutable('2024-02-15T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $walletA,
            category: $transportCategory,
            type: TransactionType::EXPENSE,
            title: 'Inna kategoria',
            transactionDate: new \DateTimeImmutable('2024-02-15T10:00:00+00:00'),
        );

        $response = $this->getJson(
            sprintf(
                '/api/transactions?type=expense&walletId=%d&categoryId=%d&from=2024-02-01&to=2024-02-29T23:59:59',
                $walletA->getId(),
                $foodCategory->getId(),
            ),
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(
            ['Pasująca'],
            array_column($data['items'], 'title'),
        );

        self::assertSame(1, $data['pagination']['totalItems']);
    }

    public function testDeletingWalletRemovesAssignedTransactions(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);

        $transaction = $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            title: 'Transakcja do usunięcia',
        );

        $response = $this->deleteJson(
            sprintf('/api/wallets/%d', $wallet->getId()),
        );

        self::assertSame(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode(),
        );

        self::assertNull(
            $this->entityManager
                ->getRepository(Transaction::class)
                ->find($transaction->getId()),
        );
    }
}
