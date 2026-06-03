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

use App\Category\Enum\CategoryType;
use App\Tests\Support\TransactionFunctionalTestCase;
use App\Transaction\Entity\Transaction;
use App\Wallet\Entity\Wallet;
use Symfony\Component\HttpFoundation\Response;

final class UpdateTransactionTest extends TransactionFunctionalTestCase
{
    public function testAuthenticatedUserCanUpdateTitle(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 50000);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($wallet, $category, [
            'amount' => 1000,
            'title' => 'Stary tytuł',
        ]);

        $response = $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), [
            'title' => 'Nowy tytuł',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('Nowy tytuł', $data['title']);
        self::assertSame(1000, $data['amount']);

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(49000, $walletFresh->getBalanceAmount());
    }

    public function testUpdateAmountAdjustsWalletBalance(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($wallet, $category, ['amount' => 1000]);

        $response = $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), [
            'amount' => 5000,
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(95000, $walletFresh->getBalanceAmount());
    }

    public function testUpdateTypeFlipsBalanceImpact(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $expenseCategory = $this->createUserCategory(user: $user, name: 'Wydatki', type: CategoryType::EXPENSE);
        $incomeCategory = $this->createUserCategory(user: $user, name: 'Pensja', type: CategoryType::INCOME);

        $transaction = $this->createTransactionThroughApi($wallet, $expenseCategory, ['amount' => 1000]);

        $response = $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), [
            'type' => 'income',
            'categoryId' => $incomeCategory->getId(),
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(101000, $walletFresh->getBalanceAmount());
    }

    public function testUpdateWithEmptyPayloadReturnsBadRequest(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($wallet, $category);

        $response = $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), []);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testCannotUpdateAnotherUserTransaction(): void
    {
        $owner = $this->createUser(email: 'owner@example.com', username: 'owner');
        $foreignWallet = $this->createWallet(user: $owner);
        $foreignCategory = $this->createUserCategory(user: $owner);
        $transaction = $this->createTransaction(
            user: $owner,
            wallet: $foreignWallet,
            category: $foreignCategory,
            title: 'Cudza',
        );

        $this->authenticateUser(email: 'intruder@example.com', username: 'intruder');

        $response = $this->patchJson(sprintf('/api/transactions/%d', $transaction->getId() ?? 0), [
            'title' => 'Przejęta',
        ]);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        /** @var Transaction $unchanged */
        $unchanged = $this->findTransactionFresh($transaction->getId() ?? 0);

        self::assertSame('Cudza', $unchanged->getTitle());
    }

    public function testCannotUpdateWithCategoryTypeMismatch(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $expenseCategory = $this->createUserCategory(user: $user, name: 'Wydatki', type: CategoryType::EXPENSE);
        $incomeCategory = $this->createUserCategory(user: $user, name: 'Pensja', type: CategoryType::INCOME);

        $transaction = $this->createTransactionThroughApi($wallet, $expenseCategory, ['amount' => 1000]);

        $response = $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), [
            'categoryId' => $incomeCategory->getId(),
        ]);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(99000, $walletFresh->getBalanceAmount());
    }

    public function testWalletSwitchMovesBalanceImpact(): void
    {
        $user = $this->authenticateUser();
        $walletA = $this->createWallet(user: $user, name: 'A', balanceAmount: 100000);
        $walletB = $this->createWallet(user: $user, name: 'B', balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user, type: CategoryType::EXPENSE);

        $transaction = $this->createTransactionThroughApi($walletA, $category, ['amount' => 7500]);

        $response = $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), [
            'walletId' => $walletB->getId(),
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        /** @var Wallet $aFresh */
        $aFresh = $this->findWalletFresh($walletA->getId() ?? 0);
        /** @var Wallet $bFresh */
        $bFresh = $this->findWalletFresh($walletB->getId() ?? 0);

        self::assertSame(100000, $aFresh->getBalanceAmount());
        self::assertSame(92500, $bFresh->getBalanceAmount());
    }

    public function testCannotSwitchToAnotherUserWallet(): void
    {
        $owner = $this->createUser(email: 'owner@example.com', username: 'owner');
        $foreignWallet = $this->createWallet(user: $owner, name: 'Cudzy');

        $user = $this->authenticateUser(email: 'me@example.com', username: 'me');
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($wallet, $category, ['amount' => 1000]);

        $response = $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), [
            'walletId' => $foreignWallet->getId(),
        ]);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(99000, $walletFresh->getBalanceAmount());
    }

    public function testGuestCannotUpdateTransaction(): void
    {
        $owner = $this->createUser(email: 'owner@example.com', username: 'owner');
        $wallet = $this->createWallet(user: $owner);
        $category = $this->createUserCategory(user: $owner);
        $transaction = $this->createTransaction(user: $owner, wallet: $wallet, category: $category);

        $response = $this->patchJson(sprintf('/api/transactions/%d', $transaction->getId() ?? 0), [
            'title' => 'Hack',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testUpdateTransactionWithMalformedJsonReturnsBadRequest(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($wallet, $category);

        $response = $this->patchMalformedJson(
            sprintf('/api/transactions/%d', $transaction['id']),
            '{"title": "Nowy"',
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
