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

use App\Entity\User;
use App\Wallet\Entity\Wallet;
use App\Wallet\Enum\CurrencyCode;
use App\Wallet\Enum\WalletType;
use App\Wallet\Repository\WalletRepository;
use JsonException;
use Symfony\Component\HttpFoundation\Response;

abstract class WalletFunctionalTestCase extends FunctionalTestCase
{
    protected function authenticateUser(
        string $email = 'wallet-owner@example.com',
        string $username = 'wallet-owner',
    ): User {
        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->postJson('/api/pin/setup', [
            'pin' => '123456',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

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

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     * @throws JsonException
     */
    protected function createWalletThroughApi(array $payload = []): array
    {
        $response = $this->postJson('/api/wallets', $payload + [
                'name' => 'Gotówka',
                'type' => 'cash',
                'currency' => 'PLN',
                'balanceAmount' => 50000,
            ]);

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

    protected function findWallet(int $id): ?Wallet
    {
        return static::getContainer()
            ->get(WalletRepository::class)
            ->find($id);
    }

    protected function findWalletFresh(int $id): ?Wallet
    {
        $this->entityManager->clear();

        return $this->findWallet($id);
    }

    /**
     * @return list<Wallet>
     */
    protected function findWalletsForUser(User $user): array
    {
        /** @var list<Wallet> $wallets */
        $wallets = static::getContainer()
            ->get(WalletRepository::class)
            ->findBy(['user' => $user]);

        return $wallets;
    }
}