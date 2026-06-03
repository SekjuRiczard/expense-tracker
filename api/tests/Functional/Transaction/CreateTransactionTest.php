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
use App\Transaction\Enum\TransactionType;
use App\Wallet\Entity\Wallet;
use Symfony\Component\HttpFoundation\Response;

final class CreateTransactionTest extends TransactionFunctionalTestCase
{
    public function testAuthenticatedUserCanCreateExpenseTransaction(): void
    {
        $user = $this->authenticateUser();
        /** @var Wallet $wallet */
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user, name: 'Subskrypcje', type: CategoryType::EXPENSE);

        $response = $this->postJson('/api/transactions', [
            'walletId' => $wallet->getId(),
            'categoryId' => $category->getId(),
            'type' => 'expense',
            'amount' => 2500,
            'title' => 'Netflix',
            'description' => 'Subskrypcja miesięczna',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertIsInt($data['id']);
        self::assertSame($wallet->getId(), $data['walletId']);
        self::assertSame($category->getId(), $data['categoryId']);
        self::assertSame('expense', $data['type']);
        self::assertSame(2500, $data['amount']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame('Netflix', $data['title']);
        self::assertSame('Subskrypcja miesięczna', $data['description']);

        $transaction = $this->findTransactionFresh($data['id']);

        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame((string) $user->getId(), (string) $transaction->getUser()->getId());
        self::assertSame(TransactionType::EXPENSE->value, $transaction->getType()->value);
        self::assertSame(2500, $transaction->getAmount());

        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertInstanceOf(Wallet::class, $walletFresh);
        self::assertSame(97500, $walletFresh->getBalanceAmount());
    }

    public function testAuthenticatedUserCanCreateIncomeTransaction(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 10000);
        $category = $this->createUserCategory(user: $user, name: 'Pensja', type: CategoryType::INCOME);

        $response = $this->postJson('/api/transactions', [
            'walletId' => $wallet->getId(),
            'categoryId' => $category->getId(),
            'type' => 'income',
            'amount' => 500000,
            'title' => 'Wypłata',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertInstanceOf(Wallet::class, $walletFresh);
        self::assertSame(510000, $walletFresh->getBalanceAmount());
    }

    public function testAuthenticatedUserCanCreateTransactionWithDefaultCategory(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createDefaultCategory(name: 'Jedzenie', type: CategoryType::EXPENSE);

        $response = $this->postJson('/api/transactions', [
            'walletId' => $wallet->getId(),
            'categoryId' => $category->getId(),
            'type' => 'expense',
            'amount' => 1500,
            'title' => 'Lidl',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testGuestCannotCreateTransaction(): void
    {
        $response = $this->postJson('/api/transactions', [
            'walletId' => 1,
            'categoryId' => 1,
            'type' => 'expense',
            'amount' => 1000,
            'title' => 'Zakupy',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testCannotCreateTransactionForAnotherUserWallet(): void
    {
        $owner = $this->createUser(email: 'owner@example.com', username: 'owner');
        $foreignWallet = $this->createWallet(user: $owner);

        $user = $this->authenticateUser(email: 'intruder@example.com', username: 'intruder');
        $category = $this->createUserCategory(user: $user, type: CategoryType::EXPENSE);

        $response = $this->postJson('/api/transactions', [
            'walletId' => $foreignWallet->getId(),
            'categoryId' => $category->getId(),
            'type' => 'expense',
            'amount' => 1000,
            'title' => 'Zakupy',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ]);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $walletFresh = $this->findWalletFresh($foreignWallet->getId() ?? 0);

        self::assertInstanceOf(Wallet::class, $walletFresh);
        self::assertSame(50000, $walletFresh->getBalanceAmount());
    }

    public function testCannotCreateTransactionForAnotherUserCategory(): void
    {
        $owner = $this->createUser(email: 'owner@example.com', username: 'owner');
        $foreignCategory = $this->createUserCategory(user: $owner, name: 'Cudza', type: CategoryType::EXPENSE);

        $user = $this->authenticateUser(email: 'intruder@example.com', username: 'intruder');
        $wallet = $this->createWallet(user: $user);

        $response = $this->postJson('/api/transactions', [
            'walletId' => $wallet->getId(),
            'categoryId' => $foreignCategory->getId(),
            'type' => 'expense',
            'amount' => 1000,
            'title' => 'Zakupy',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ]);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCannotCreateTransactionWithTypeMismatch(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user, name: 'Jedzenie', type: CategoryType::EXPENSE);

        $response = $this->postJson('/api/transactions', [
            'walletId' => $wallet->getId(),
            'categoryId' => $category->getId(),
            'type' => 'income',
            'amount' => 1000,
            'title' => 'Niespójna transakcja',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ]);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertInstanceOf(Wallet::class, $walletFresh);
        self::assertSame(50000, $walletFresh->getBalanceAmount());
    }

    public function testCannotCreateTransactionWithZeroAmount(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);

        $response = $this->postJson('/api/transactions', [
            'walletId' => $wallet->getId(),
            'categoryId' => $category->getId(),
            'type' => 'expense',
            'amount' => 0,
            'title' => 'Zerowa kwota',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ]);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCannotCreateTransactionWithNegativeAmount(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $category = $this->createUserCategory(user: $user);

        $response = $this->postJson('/api/transactions', [
            'walletId' => $wallet->getId(),
            'categoryId' => $category->getId(),
            'type' => 'expense',
            'amount' => -100,
            'title' => 'Ujemna kwota',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ]);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateTransactionWithMalformedJsonReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $response = $this->postMalformedJson('/api/transactions', '{"walletId": 1');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
