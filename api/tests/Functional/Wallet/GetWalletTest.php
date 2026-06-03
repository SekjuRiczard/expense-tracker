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
use App\Wallet\Enum\CurrencyCode;
use App\Wallet\Enum\WalletType;
use Symfony\Component\HttpFoundation\Response;

final class GetWalletTest extends WalletFunctionalTestCase
{
    public function testAuthenticatedUserCanGetOwnWallet(): void
    {
        $user = $this->authenticateUser();

        $wallet = $this->createWallet(
            user: $user,
            name: 'Konto główne',
            type: WalletType::BANK_ACCOUNT,
            currency: CurrencyCode::PLN,
            balanceAmount: 12345,
        );

        $walletId = $wallet->getId();

        self::assertIsInt($walletId);

        $response = $this->getJson(sprintf('/api/wallets/%d', $walletId));

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame($walletId, $data['id']);
        self::assertSame('Konto główne', $data['name']);
        self::assertSame(WalletType::BANK_ACCOUNT->value, $data['type']);
        self::assertSame(CurrencyCode::PLN->value, $data['currency']);
        self::assertSame(12345, $data['balanceAmount']);
        self::assertArrayHasKey('createdAt', $data);
        self::assertArrayHasKey('updatedAt', $data);
    }

    public function testAuthenticatedUserCannotGetAnotherUserWallet(): void
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

        $response = $this->getJson(sprintf('/api/wallets/%d', $walletId));

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetMissingWalletReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/wallets/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetWalletWithInvalidIdReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/wallets/abc');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGuestCannotGetWallet(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $wallet = $this->createWallet(user: $owner);
        $walletId = $wallet->getId();

        self::assertIsInt($walletId);

        $response = $this->getJson(sprintf('/api/wallets/%d', $walletId));

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
