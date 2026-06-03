<?php

/*
 * This file is part of the Expense Tracker.
 *
 * (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Transaction\Action;

use App\Entity\User;
use App\Transaction\Entity\Transaction;
use App\Transaction\Provider\TransactionResourceProvider;
use App\Transaction\Repository\TransactionRepository;
use App\Transaction\Service\WalletBalanceService;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DeleteTransactionAction
{
    public function __construct(
        private TransactionResourceProvider $resourceProvider,
        private WalletBalanceService $walletBalanceService,
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(int $id, User $user): void
    {
        /** @var Transaction $transaction */
        $transaction = $this->resourceProvider->getTransaction($id, $user);
        $this->entityManager->wrapInTransaction(
            function () use ($transaction): void {
                $this->walletBalanceService->revertTransaction($transaction);
                $this->transactionRepository->remove($transaction);
            },
        );
    }
}
