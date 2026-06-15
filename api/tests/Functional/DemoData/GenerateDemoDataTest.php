<?php

declare(strict_types=1);

namespace App\Tests\Functional\DemoData;

use App\Tests\Support\DemoDataFunctionalTestCase;
use App\Transaction\Entity\Transaction;
use App\Transaction\Enum\TransactionType;
use App\Wallet\Entity\Wallet;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class GenerateDemoDataTest extends DemoDataFunctionalTestCase
{
    public function testAdminCanGenerateLargeDemoDataSet(): void
    {
        $user = $this->authenticateAdmin();

        $response = $this->postJson('/api/admin/demo-data', []);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertIsInt($data['seed']);
        self::assertSame(12, $data['monthsGenerated']);
        self::assertIsInt($data['defaultCategoriesCreated']);
        self::assertSame(4, $data['walletsCreated']);
        self::assertSame(12, $data['budgetsCreated']);
        self::assertGreaterThan(300, $data['transactionsCreated']);
        self::assertLessThan(700, $data['transactionsCreated']);

        $wallets = $this->findWalletsForUser($user);
        $budgets = $this->findBudgetsForUser($user);
        $transactions = $this->findTransactionsForUser($user);

        self::assertCount(4, $wallets);
        self::assertCount(12, $budgets);
        self::assertCount($data['transactionsCreated'], $transactions);

        $months = [];

        foreach ($transactions as $transaction) {
            $months[$transaction->getTransactionDate()->format('Y-m')] = true;

            self::assertSame(
                $transaction->getCategory()->getType()->value,
                $transaction->getType()->value,
            );

            self::assertLessThanOrEqual(
                new DateTimeImmutable('today 23:59:59'),
                $transaction->getTransactionDate(),
            );
        }

        self::assertCount(12, $months);

        $this->assertWalletBalancesAreConsistent(
            wallets: $wallets,
            transactions: $transactions,
        );
    }

    public function testCannotGenerateDemoDataTwiceWithoutClearing(): void
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/admin/demo-data', []);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $response = $this->postJson('/api/admin/demo-data', []);

        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
    }

    public function testAdminCanClearAndGenerateDemoDataAgain(): void
    {
        $user = $this->authenticateAdmin();

        $response = $this->postJson('/api/admin/demo-data', []);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $generatedData = $this->jsonResponse();

        $response = $this->deleteJson('/api/admin/demo-data');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $clearedData = $this->jsonResponse();

        self::assertSame(
            $generatedData['transactionsCreated'],
            $clearedData['transactionsDeleted'],
        );

        self::assertSame(12, $clearedData['budgetsDeleted']);
        self::assertSame(4, $clearedData['walletsDeleted']);

        self::assertCount(0, $this->findTransactionsForUser($user));
        self::assertCount(0, $this->findBudgetsForUser($user));
        self::assertCount(0, $this->findWalletsForUser($user));

        $response = $this->postJson('/api/admin/demo-data', []);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testRegularUserCannotGenerateDemoData(): void
    {
        $user = $this->authenticateRegularUser();

        $response = $this->postJson('/api/admin/demo-data', []);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        self::assertCount(0, $this->findTransactionsForUser($user));
        self::assertCount(0, $this->findBudgetsForUser($user));
        self::assertCount(0, $this->findWalletsForUser($user));
    }

    public function testGuestCannotGenerateDemoData(): void
    {
        $response = $this->postJson('/api/admin/demo-data', []);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @param list<Wallet>      $wallets
     * @param list<Transaction> $transactions
     */
    private function assertWalletBalancesAreConsistent(
        array $wallets,
        array $transactions,
    ): void {
        $expectedBalances = [
            'Konto główne' => 450_000,
            'Gotówka' => 500_000,
            'Oszczędności' => 2_000_000,
            'Karta kredytowa' => 0,
        ];

        foreach ($transactions as $transaction) {
            $walletName = $transaction->getWallet()->getName();

            self::assertArrayHasKey($walletName, $expectedBalances);

            $expectedBalances[$walletName] += match ($transaction->getType()) {
                TransactionType::INCOME => $transaction->getAmount(),
                TransactionType::EXPENSE => -$transaction->getAmount(),
            };
        }

        foreach ($wallets as $wallet) {
            self::assertSame(
                $expectedBalances[$wallet->getName()],
                $wallet->getBalanceAmount(),
                sprintf(
                    'Wallet balance is invalid for wallet "%s".',
                    $wallet->getName(),
                ),
            );
        }
    }
}