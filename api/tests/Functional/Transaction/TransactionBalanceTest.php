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
use App\Wallet\Entity\Wallet;
use Symfony\Component\HttpFoundation\Response;

final class TransactionBalanceTest extends TransactionFunctionalTestCase
{
    public function testExpenseCreateDecreasesBalance(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user, type: CategoryType::EXPENSE);

        $this->createTransactionThroughApi($wallet, $category, ['amount' => 12345]);

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(87655, $walletFresh->getBalanceAmount());
    }

    public function testIncomeCreateIncreasesBalance(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user, name: 'Pensja', type: CategoryType::INCOME);

        $this->createTransactionThroughApi($wallet, $category, ['amount' => 250000]);

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(350000, $walletFresh->getBalanceAmount());
    }

    public function testDeleteRestoresBalance(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($wallet, $category, ['amount' => 4200]);

        $response = $this->deleteJson(sprintf('/api/transactions/%d', $transaction['id']));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(100000, $walletFresh->getBalanceAmount());
    }

    public function testAmountUpdateRecalculatesBalance(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($wallet, $category, ['amount' => 1000]);

        $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), ['amount' => 7500]);

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(92500, $walletFresh->getBalanceAmount());
    }

    public function testTypeSwitchFlipsBalance(): void
    {
        $user = $this->authenticateUser();
        $wallet = $this->createWallet(user: $user, balanceAmount: 100000);
        $expenseCategory = $this->createUserCategory(user: $user, name: 'Wydatki', type: CategoryType::EXPENSE);
        $incomeCategory = $this->createUserCategory(user: $user, name: 'Pensja', type: CategoryType::INCOME);

        $transaction = $this->createTransactionThroughApi($wallet, $expenseCategory, ['amount' => 5000]);

        $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), [
            'type' => 'income',
            'categoryId' => $incomeCategory->getId(),
        ]);

        /** @var Wallet $walletFresh */
        $walletFresh = $this->findWalletFresh($wallet->getId() ?? 0);

        self::assertSame(105000, $walletFresh->getBalanceAmount());
    }

    public function testWalletSwitchMovesBalance(): void
    {
        $user = $this->authenticateUser();
        $walletA = $this->createWallet(user: $user, name: 'A', balanceAmount: 100000);
        $walletB = $this->createWallet(user: $user, name: 'B', balanceAmount: 200000);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($walletA, $category, ['amount' => 3000]);

        $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), [
            'walletId' => $walletB->getId(),
        ]);

        /** @var Wallet $aFresh */
        $aFresh = $this->findWalletFresh($walletA->getId() ?? 0);
        /** @var Wallet $bFresh */
        $bFresh = $this->findWalletFresh($walletB->getId() ?? 0);

        self::assertSame(100000, $aFresh->getBalanceAmount());
        self::assertSame(197000, $bFresh->getBalanceAmount());
    }

    public function testWalletSwitchAndAmountChangeRecalculatesBoth(): void
    {
        $user = $this->authenticateUser();
        $walletA = $this->createWallet(user: $user, name: 'A', balanceAmount: 100000);
        $walletB = $this->createWallet(user: $user, name: 'B', balanceAmount: 100000);
        $category = $this->createUserCategory(user: $user);
        $transaction = $this->createTransactionThroughApi($walletA, $category, ['amount' => 1000]);

        $this->patchJson(sprintf('/api/transactions/%d', $transaction['id']), [
            'walletId' => $walletB->getId(),
            'amount' => 5000,
        ]);

        /** @var Wallet $aFresh */
        $aFresh = $this->findWalletFresh($walletA->getId() ?? 0);
        /** @var Wallet $bFresh */
        $bFresh = $this->findWalletFresh($walletB->getId() ?? 0);

        self::assertSame(100000, $aFresh->getBalanceAmount());
        self::assertSame(95000, $bFresh->getBalanceAmount());
    }
}
