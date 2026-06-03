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
use App\Wallet\Entity\Wallet;
use Symfony\Component\HttpFoundation\Response;

final class DeleteTransactionTest extends TransactionFunctionalTestCase
{
    public function testAuthenticatedUserCanDeleteOwnTransaction(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($wallet, $category, ['amount' => 2500]);

        $response = $this->deleteJson(sprintf('/api/transactions/%d', $transaction['id']));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertSame('', $response->getContent());
        self::assertNull($this->findTransactionFresh($transaction['id']));

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(100000, $walletFresh->getBalanceAmount());
    }

    public function testAuthenticatedUserCannotDeleteAnotherUserTransaction(): void
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

        $response = $this->deleteJson(sprintf('/api/transactions/%d', $transactionId));

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertNotNull($this->findTransactionFresh($transactionId));
    }

    public function testDeleteMissingTransactionReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->deleteJson('/api/transactions/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGuestCannotDeleteTransaction(): void
    {
        $owner = $this->createUser(email: 'owner@example.com', username: 'owner');
        $wallet = $this->createWallet(user: $owner);
        $category = $this->createUserCategory(user: $owner);
        $transaction = $this->createTransaction(user: $owner, wallet: $wallet, category: $category);
        /** @var int $transactionId */
        $transactionId = $transaction->getId() ?? 0;

        $response = $this->deleteJson(sprintf('/api/transactions/%d', $transactionId));

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertNotNull($this->findTransactionFresh($transactionId));
    }
}
