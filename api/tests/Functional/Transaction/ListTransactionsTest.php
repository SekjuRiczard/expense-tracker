<?php

/*
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Functional\Transaction;

use App\Tests\Support\TransactionFunctionalTestCase;
use App\Transaction\Enum\TransactionType;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class ListTransactionsTest extends TransactionFunctionalTestCase
{
    public function testAuthenticatedUserListsOnlyOwnTransactions(): void
    {
        $owner = $this->createUser(email: 'owner@example.com', username: 'owner');
        $foreignWallet = $this->createWallet(user: $owner);
        $foreignCategory = $this->createUserCategory(user: $owner, name: 'Cudza');
        $this->createTransaction(
            user: $owner,
            wallet: $foreignWallet,
            category: $foreignCategory,
            title: 'Cudza transakcja',
        );

        $user = $this->authenticateUser(email: 'viewer@example.com', username: 'viewer');
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

        /** @var list<array<string, mixed>> $data */
        $data = $this->jsonResponse();
        $titles = array_column($data, 'title');

        self::assertCount(1, $data);
        self::assertContains('Moja transakcja', $titles);
        self::assertNotContains('Cudza transakcja', $titles);
    }

    public function testAuthenticatedUserWithoutTransactionsReceivesEmptyList(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/transactions');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame([], $this->jsonResponse());
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
            transactionDate: new DateTimeImmutable('2024-01-01T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            title: 'Najnowsza',
            transactionDate: new DateTimeImmutable('2024-12-31T10:00:00+00:00'),
        );

        $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            title: 'Środkowa',
            transactionDate: new DateTimeImmutable('2024-06-15T10:00:00+00:00'),
        );

        $response = $this->getJson('/api/transactions');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        /** @var list<array<string, mixed>> $data */
        $data = $this->jsonResponse();

        self::assertSame(['Najnowsza', 'Środkowa', 'Najstarsza'], array_column($data, 'title'));
    }

    public function testGuestCannotListTransactions(): void
    {
        $response = $this->getJson('/api/transactions');

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
