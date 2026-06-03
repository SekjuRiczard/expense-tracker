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

final class UpdateWalletTest extends WalletFunctionalTestCase
{
    public function testAuthenticatedUserCanUpdateWalletName(): void
    {
        $this->authenticateUser();

        $wallet = $this->createWalletThroughApi([
            'name' => 'Stara nazwa',
            'type' => 'cash',
            'currency' => 'PLN',
            'balanceAmount' => 50000,
        ]);

        $response = $this->patchJson(sprintf('/api/wallets/%d', $wallet['id']), [
            'name' => 'Nowa nazwa',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('Nowa nazwa', $data['name']);
        self::assertSame('cash', $data['type']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame(50000, $data['balanceAmount']);

        $updatedWallet = $this->findWalletFresh($wallet['id']);

        self::assertInstanceOf(Wallet::class, $updatedWallet);
        self::assertSame('Nowa nazwa', $updatedWallet->getName());
        self::assertSame(WalletType::CASH->value, $updatedWallet->getType()->value);
        self::assertSame(CurrencyCode::PLN->value, $updatedWallet->getCurrency()->value);
        self::assertSame(50000, $updatedWallet->getBalanceAmount());
    }

    public function testAuthenticatedUserCanUpdateWalletType(): void
    {
        $this->authenticateUser();

        $wallet = $this->createWalletThroughApi([
            'name' => 'Gotówka',
            'type' => 'cash',
            'currency' => 'PLN',
            'balanceAmount' => 50000,
        ]);

        $response = $this->patchJson(sprintf('/api/wallets/%d', $wallet['id']), [
            'type' => 'bank_account',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('Gotówka', $data['name']);
        self::assertSame('bank_account', $data['type']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame(50000, $data['balanceAmount']);

        $updatedWallet = $this->findWalletFresh($wallet['id']);

        self::assertInstanceOf(Wallet::class, $updatedWallet);
        self::assertSame('Gotówka', $updatedWallet->getName());
        self::assertSame(WalletType::BANK_ACCOUNT->value, $updatedWallet->getType()->value);
        self::assertSame(CurrencyCode::PLN->value, $updatedWallet->getCurrency()->value);
        self::assertSame(50000, $updatedWallet->getBalanceAmount());
    }

    public function testAuthenticatedUserCanUpdateWalletNameAndType(): void
    {
        $this->authenticateUser();

        $wallet = $this->createWalletThroughApi();

        $response = $this->patchJson(sprintf('/api/wallets/%d', $wallet['id']), [
            'name' => 'Konto oszczędnościowe',
            'type' => 'savings_account',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('Konto oszczędnościowe', $data['name']);
        self::assertSame('savings_account', $data['type']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame(50000, $data['balanceAmount']);

        $updatedWallet = $this->findWalletFresh($wallet['id']);

        self::assertInstanceOf(Wallet::class, $updatedWallet);
        self::assertSame('Konto oszczędnościowe', $updatedWallet->getName());
        self::assertSame(WalletType::SAVINGS_ACCOUNT->value, $updatedWallet->getType()->value);
        self::assertSame(CurrencyCode::PLN->value, $updatedWallet->getCurrency()->value);
        self::assertSame(50000, $updatedWallet->getBalanceAmount());
    }

    public function testUpdateWalletWithEmptyPayloadReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $wallet = $this->createWalletThroughApi([
            'name' => 'Gotówka',
        ]);

        $response = $this->patchJson(sprintf('/api/wallets/%d', $wallet['id']), []);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $unchangedWallet = $this->findWalletFresh($wallet['id']);

        self::assertInstanceOf(Wallet::class, $unchangedWallet);
        self::assertSame('Gotówka', $unchangedWallet->getName());
        self::assertSame(WalletType::CASH->value, $unchangedWallet->getType()->value);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('invalidValidationPayloadProvider')]
    public function testUpdateWalletWithInvalidValidationPayloadReturnsValidationError(array $payload): void
    {
        $this->authenticateUser();

        $wallet = $this->createWalletThroughApi([
            'name' => 'Gotówka',
            'type' => 'cash',
        ]);

        $response = $this->patchJson(sprintf('/api/wallets/%d', $wallet['id']), $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $unchangedWallet = $this->findWalletFresh($wallet['id']);

        self::assertInstanceOf(Wallet::class, $unchangedWallet);
        self::assertSame('Gotówka', $unchangedWallet->getName());
        self::assertSame(WalletType::CASH->value, $unchangedWallet->getType()->value);
    }

    public function testUpdateWalletWithInvalidTypeReturnsValidationError(): void
    {
        $this->authenticateUser();

        $wallet = $this->createWalletThroughApi([
            'name' => 'Gotówka',
            'type' => 'cash',
        ]);

        $response = $this->patchJson(sprintf('/api/wallets/%d', $wallet['id']), [
            'type' => 'invalid_type',
        ]);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $unchangedWallet = $this->findWalletFresh($wallet['id']);

        self::assertInstanceOf(Wallet::class, $unchangedWallet);
        self::assertSame('Gotówka', $unchangedWallet->getName());
        self::assertSame(WalletType::CASH->value, $unchangedWallet->getType()->value);
    }

    public function testUpdateWalletCannotChangeCurrency(): void
    {
        $this->authenticateUser();

        $wallet = $this->createWalletThroughApi([
            'currency' => 'PLN',
        ]);

        $response = $this->patchJson(sprintf('/api/wallets/%d', $wallet['id']), [
            'currency' => 'EUR',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $unchangedWallet = $this->findWalletFresh($wallet['id']);

        self::assertInstanceOf(Wallet::class, $unchangedWallet);
        self::assertSame(CurrencyCode::PLN->value, $unchangedWallet->getCurrency()->value);
    }

    public function testUpdateWalletCannotChangeBalanceAmount(): void
    {
        $this->authenticateUser();

        $wallet = $this->createWalletThroughApi([
            'balanceAmount' => 50000,
        ]);

        $response = $this->patchJson(sprintf('/api/wallets/%d', $wallet['id']), [
            'balanceAmount' => 999999,
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $unchangedWallet = $this->findWalletFresh($wallet['id']);

        self::assertInstanceOf(Wallet::class, $unchangedWallet);
        self::assertSame(50000, $unchangedWallet->getBalanceAmount());
    }

    public function testAuthenticatedUserCannotUpdateAnotherUserWallet(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $wallet = $this->createWallet(
            user: $owner,
            name: 'Cudzy portfel',
        );

        $walletId = $wallet->getId();

        self::assertIsInt($walletId);

        $this->authenticateUser(
            email: 'intruder@example.com',
            username: 'intruder',
        );

        $response = $this->patchJson(sprintf('/api/wallets/%d', $walletId), [
            'name' => 'Przejęty portfel',
        ]);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $unchangedWallet = $this->findWalletFresh($walletId);

        self::assertInstanceOf(Wallet::class, $unchangedWallet);
        self::assertSame('Cudzy portfel', $unchangedWallet->getName());
    }

    public function testUpdateMissingWalletReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->patchJson('/api/wallets/999999', [
            'name' => 'Nowa nazwa',
        ]);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGuestCannotUpdateWallet(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $wallet = $this->createWallet(
            user: $owner,
            name: 'Gotówka',
        );

        $walletId = $wallet->getId();

        self::assertIsInt($walletId);

        $response = $this->patchJson(sprintf('/api/wallets/%d', $walletId), [
            'name' => 'Nowa nazwa',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $unchangedWallet = $this->findWalletFresh($walletId);

        self::assertInstanceOf(Wallet::class, $unchangedWallet);
        self::assertSame('Gotówka', $unchangedWallet->getName());
    }

    public function testUpdateWalletWithMalformedJsonReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $wallet = $this->createWalletThroughApi([
            'name' => 'Gotówka',
        ]);

        $response = $this->patchMalformedJson(
            sprintf('/api/wallets/%d', $wallet['id']),
            '{"name": "Nowa nazwa"',
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $unchangedWallet = $this->findWalletFresh($wallet['id']);

        self::assertInstanceOf(Wallet::class, $unchangedWallet);
        self::assertSame('Gotówka', $unchangedWallet->getName());
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>}>
     */
    public static function invalidValidationPayloadProvider(): iterable
    {
        yield 'blank name' => [
            'payload' => [
                'name' => '',
            ],
        ];

        yield 'too long name' => [
            'payload' => [
                'name' => str_repeat('a', 256),
            ],
        ];
    }
}
