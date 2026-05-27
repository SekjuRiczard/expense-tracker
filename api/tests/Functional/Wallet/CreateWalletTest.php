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

namespace App\Tests\Functional\Wallet;

use App\Tests\Support\WalletFunctionalTestCase;
use App\Wallet\Entity\Wallet;
use App\Wallet\Enum\CurrencyCode;
use App\Wallet\Enum\WalletType;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class CreateWalletTest extends WalletFunctionalTestCase
{
    public function testAuthenticatedUserCanCreateWallet(): void
    {
        $user = $this->authenticateUser();

        $response = $this->postJson('/api/wallets', [
            'name' => 'Gotówka',
            'type' => 'cash',
            'currency' => 'PLN',
            'balanceAmount' => 50000,
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertIsInt($data['id']);
        self::assertSame('Gotówka', $data['name']);
        self::assertSame('cash', $data['type']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame(50000, $data['balanceAmount']);
        self::assertArrayHasKey('createdAt', $data);
        self::assertArrayHasKey('updatedAt', $data);

        $wallet = $this->findWalletFresh($data['id']);

        self::assertInstanceOf(Wallet::class, $wallet);
        self::assertSame((string) $user->getId(), (string) $wallet->getUser()->getId());
        self::assertSame('Gotówka', $wallet->getName());
        self::assertSame(WalletType::CASH->value, $wallet->getType()->value);
        self::assertSame(CurrencyCode::PLN->value, $wallet->getCurrency()->value);
        self::assertSame(50000, $wallet->getBalanceAmount());
    }

    public function testAuthenticatedUserCanCreateWalletWithZeroBalance(): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/wallets', [
            'name' => 'Pusty portfel',
            'type' => 'cash',
            'currency' => 'PLN',
            'balanceAmount' => 0,
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(0, $data['balanceAmount']);

        $wallet = $this->findWalletFresh($data['id']);

        self::assertInstanceOf(Wallet::class, $wallet);
        self::assertSame(0, $wallet->getBalanceAmount());
    }

    public function testAuthenticatedUserCanCreateWalletWithoutBalanceAmount(): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/wallets', [
            'name' => 'Gotówka',
            'type' => 'cash',
            'currency' => 'PLN',
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(0, $data['balanceAmount']);

        $wallet = $this->findWalletFresh($data['id']);

        self::assertInstanceOf(Wallet::class, $wallet);
        self::assertSame(0, $wallet->getBalanceAmount());
    }

    public function testGuestCannotCreateWallet(): void
    {
        $response = $this->postJson('/api/wallets', [
            'name' => 'Gotówka',
            'type' => 'cash',
            'currency' => 'PLN',
            'balanceAmount' => 50000,
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $wallets = $this->entityManager
            ->getRepository(Wallet::class)
            ->findAll();

        self::assertCount(0, $wallets);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('invalidPayloadProvider')]
    public function testCreateWalletWithInvalidPayloadReturnsValidationError(array $payload): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/wallets', $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $wallets = $this->entityManager
            ->getRepository(Wallet::class)
            ->findAll();

        self::assertCount(0, $wallets);
    }

    public function testCreateWalletWithMalformedJsonReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $response = $this->postMalformedJson('/api/wallets', '{"name": "Gotówka"');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $wallets = $this->entityManager
            ->getRepository(Wallet::class)
            ->findAll();

        self::assertCount(0, $wallets);
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>}>
     */
    public static function invalidPayloadProvider(): iterable
    {
        yield 'blank name' => [
            'payload' => [
                'name' => '',
                'type' => 'cash',
                'currency' => 'PLN',
                'balanceAmount' => 50000,
            ],
        ];

        yield 'too long name' => [
            'payload' => [
                'name' => str_repeat('a', 256),
                'type' => 'cash',
                'currency' => 'PLN',
                'balanceAmount' => 50000,
            ],
        ];

        yield 'negative balance amount' => [
            'payload' => [
                'name' => 'Gotówka',
                'type' => 'cash',
                'currency' => 'PLN',
                'balanceAmount' => -1,
            ],
        ];

        yield 'missing name' => [
            'payload' => [
                'type' => 'cash',
                'currency' => 'PLN',
                'balanceAmount' => 50000,
            ],
        ];

        yield 'missing type' => [
            'payload' => [
                'name' => 'Gotówka',
                'currency' => 'PLN',
                'balanceAmount' => 50000,
            ],
        ];

        yield 'missing currency' => [
            'payload' => [
                'name' => 'Gotówka',
                'type' => 'cash',
                'balanceAmount' => 50000,
            ],
        ];

        yield 'invalid type' => [
            'payload' => [
                'name' => 'Gotówka',
                'type' => 'invalid_type',
                'currency' => 'PLN',
                'balanceAmount' => 50000,
            ],
        ];

        yield 'invalid currency' => [
            'payload' => [
                'name' => 'Gotówka',
                'type' => 'cash',
                'currency' => 'JPY',
                'balanceAmount' => 50000,
            ],
        ];
    }
}