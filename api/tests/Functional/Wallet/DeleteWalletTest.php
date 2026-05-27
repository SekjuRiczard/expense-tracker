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
use Symfony\Component\HttpFoundation\Response;

final class DeleteWalletTest extends WalletFunctionalTestCase
{
    public function testAuthenticatedUserCanDeleteOwnWallet(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user);
        $walletId = $wallet->getId();

        self::assertIsInt($walletId);

        $response = $this->deleteJson(sprintf('/api/wallets/%d', $walletId));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertSame('', $response->getContent());
        self::assertNull($this->findWalletFresh($walletId));
    }

    public function testAuthenticatedUserCannotDeleteAnotherUserWallet(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $wallet = $this->createWallet(user: $owner);
        $walletId = $wallet->getId();

        self::assertIsInt($walletId);

        $this->authenticateUser(
            email: 'intruder@example.com',
            username: 'intruder',
        );

        $response = $this->deleteJson(sprintf('/api/wallets/%d', $walletId));

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertNotNull($this->findWalletFresh($walletId));
    }

    public function testDeleteMissingWalletReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->deleteJson('/api/wallets/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGuestCannotDeleteWallet(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $wallet = $this->createWallet(user: $owner);
        $walletId = $wallet->getId();

        self::assertIsInt($walletId);

        $response = $this->deleteJson(sprintf('/api/wallets/%d', $walletId));

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertNotNull($this->findWalletFresh($walletId));
    }

    public function testDeletingOneWalletDoesNotDeleteAnotherWallet(): void
    {
        $user = $this->authenticateUser();

        $walletToDelete = $this->createWallet(
            user: $user,
            name: 'Do usunięcia',
        );

        $walletToKeep = $this->createWallet(
            user: $user,
            name: 'Ma zostać',
        );

        $walletToDeleteId = $walletToDelete->getId();
        $walletToKeepId = $walletToKeep->getId();

        self::assertIsInt($walletToDeleteId);
        self::assertIsInt($walletToKeepId);

        $response = $this->deleteJson(sprintf('/api/wallets/%d', $walletToDeleteId));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertNull($this->findWalletFresh($walletToDeleteId));
        self::assertNotNull($this->findWalletFresh($walletToKeepId));
    }
}