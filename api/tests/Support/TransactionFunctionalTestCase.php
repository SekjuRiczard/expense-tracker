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

namespace App\Tests\Support;

use App\Category\Entity\Category;
use App\Category\Enum\CategoryType;
use App\Category\Repository\CategoryRepository;
use App\Entity\User;
use App\Transaction\Entity\Transaction;
use App\Transaction\Enum\TransactionType;
use App\Transaction\Repository\TransactionRepository;
use App\Wallet\Entity\Wallet;
use App\Wallet\Enum\CurrencyCode;
use App\Wallet\Enum\WalletType;
use App\Wallet\Repository\WalletRepository;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

abstract class TransactionFunctionalTestCase extends FunctionalTestCase
{
    protected const DEFAULT_TRANSACTION_DATE = '2024-06-15T12:00:00+00:00';

    protected function authenticateUser(
        string $email = 'transaction-owner@example.com',
        string $username = 'transaction-owner',
    ): User {
        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'Password123!',
        ]);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->postJson('/api/pin/setup', ['pin' => '123456']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        /** @var User|null $user */
        $user = $this->findUserByEmail($email);
        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    protected function createWallet(
        User $user,
        string $name = 'Gotówka',
        WalletType $type = WalletType::CASH,
        CurrencyCode $currency = CurrencyCode::PLN,
        int $balanceAmount = 50000,
    ): Wallet {
        /** @var Wallet $wallet */
        $wallet = new Wallet(
            user: $user,
            name: $name,
            type: $type,
            currency: $currency,
            balanceAmount: $balanceAmount,
        );
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();

        return $wallet;
    }

    protected function createCategory(
        ?User $user,
        string $name = 'Subskrypcje',
        CategoryType $type = CategoryType::EXPENSE,
        bool $isDefault = false,
    ): Category {
        /** @var Category $category */
        $category = new Category(
            user: $user,
            name: $name,
            type: $type,
            isDefault: $isDefault,
        );
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    protected function createDefaultCategory(
        string $name = 'Jedzenie',
        CategoryType $type = CategoryType::EXPENSE,
    ): Category {
        return $this->createCategory(user: null, name: $name, type: $type, isDefault: true);
    }

    protected function createUserCategory(
        User $user,
        string $name = 'Subskrypcje',
        CategoryType $type = CategoryType::EXPENSE,
    ): Category {
        return $this->createCategory(user: $user, name: $name, type: $type, isDefault: false);
    }

    protected function createTransaction(
        User $user,
        Wallet $wallet,
        Category $category,
        TransactionType $type = TransactionType::EXPENSE,
        int $amount = 1000,
        string $title = 'Zakupy',
        ?string $description = null,
        ?DateTimeImmutable $transactionDate = null,
    ): Transaction {
        /** @var Transaction $transaction */
        $transaction = new Transaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: $type,
            amount: $amount,
            title: $title,
            description: $description,
            transactionDate: $transactionDate ?? new DateTimeImmutable(self::DEFAULT_TRANSACTION_DATE),
        );
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $transaction;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    protected function createTransactionThroughApi(
        Wallet $wallet,
        Category $category,
        array $overrides = [],
    ): array {
        $payload = $overrides + [
            'walletId' => $wallet->getId(),
            'categoryId' => $category->getId(),
            'type' => $category->getType()->value,
            'amount' => 1000,
            'title' => 'Zakupy',
            'transactionDate' => self::DEFAULT_TRANSACTION_DATE,
        ];
        $response = $this->postJson('/api/transactions', $payload);
        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();
        self::assertIsInt($data['id']);

        return $data;
    }

    protected function getJson(string $uri): Response
    {
        $this->client->request(
            method: 'GET',
            uri: $uri,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $this->clientIp,
            ],
        );

        return $this->client->getResponse();
    }

    protected function deleteJson(string $uri): Response
    {
        $this->client->request(
            method: 'DELETE',
            uri: $uri,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $this->clientIp,
            ],
        );

        return $this->client->getResponse();
    }

    protected function findTransaction(int $id): ?Transaction
    {
        return static::getContainer()->get(TransactionRepository::class)->find($id);
    }

    protected function findTransactionFresh(int $id): ?Transaction
    {
        $this->entityManager->clear();

        return $this->findTransaction($id);
    }

    protected function findWalletFresh(int $id): ?Wallet
    {
        $this->entityManager->clear();

        return static::getContainer()->get(WalletRepository::class)->find($id);
    }

    protected function findCategoryFresh(int $id): ?Category
    {
        $this->entityManager->clear();

        return static::getContainer()->get(CategoryRepository::class)->find($id);
    }
}
