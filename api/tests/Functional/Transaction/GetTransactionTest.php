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
use Symfony\Component\HttpFoundation\Response;

final class GetTransactionTest extends TransactionFunctionalTestCase
{
    public function testAuthenticatedUserCanGetOwnTransaction(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: TransactionType::EXPENSE,
            amount: 1234,
            title: 'Mój wpis',
        );
        /** @var int $transactionId */
        $transactionId = $transaction->getId() ?? 0;

        $response = $this->getJson(sprintf('/api/transactions/%d', $transactionId));

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame($transactionId, $data['id']);
        self::assertSame('Mój wpis', $data['title']);
        self::assertSame('expense', $data['type']);
        self::assertSame(1234, $data['amount']);
        self::assertSame($wallet->getId(), $data['walletId']);
        self::assertSame($category->getId(), $data['categoryId']);
    }

    public function testAuthenticatedUserCannotGetAnotherUserTransaction(): void
    {
        $owner = $this->createUser(email: 'owner@example.com', username: 'owner');
        $foreignWallet = $this->createWallet(user: $owner);
        $foreignCategory = $this->createUserCategory(user: $owner);
        $transaction = $this->createTransaction(
            user: $owner,
            wallet: $foreignWallet,
            category: $foreignCategory,
        );
        /** @var int $transactionId */
        $transactionId = $transaction->getId() ?? 0;

        $this->authenticateUser(email: 'intruder@example.com', username: 'intruder');

        $response = $this->getJson(sprintf('/api/transactions/%d', $transactionId));

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetMissingTransactionReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/transactions/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetTransactionWithInvalidIdReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/transactions/abc');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGuestCannotGetTransaction(): void
    {
        $owner = $this->createUser(email: 'owner@example.com', username: 'owner');
        $wallet = $this->createWallet(user: $owner);
        $category = $this->createUserCategory(user: $owner);
        $transaction = $this->createTransaction(
            user: $owner,
            wallet: $wallet,
            category: $category,
        );
        /** @var int $transactionId */
        $transactionId = $transaction->getId() ?? 0;

        $response = $this->getJson(sprintf('/api/transactions/%d', $transactionId));

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
