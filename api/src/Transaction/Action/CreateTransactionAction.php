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

use App\Category\Entity\Category;
use App\Entity\User;
use App\Transaction\Dto\Request\CreateTransactionRequest;
use App\Transaction\Dto\Response\TransactionResponse;
use App\Transaction\Entity\Transaction;
use App\Transaction\Provider\TransactionResourceProvider;
use App\Transaction\Repository\TransactionRepository;
use App\Transaction\Service\WalletBalanceService;
use App\Transaction\Validator\TransactionValidator;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CreateTransactionAction
{
    public function __construct(
        private TransactionResourceProvider $resourceProvider,
        private TransactionValidator $transactionValidator,
        private WalletBalanceService $walletBalanceService,
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(CreateTransactionRequest $request, User $user): TransactionResponse
    {
        /** @var Category $category */
        $category = $this->resourceProvider->getCategory($request->categoryId, $user);
        $this->transactionValidator->validateCategoryType($category, $request->type);
        /** @var Transaction $transaction */
        $transaction = new Transaction(
            user: $user,
            wallet: $this->resourceProvider->getWallet($request->walletId, $user),
            category: $category,
            type: $request->type,
            amount: $request->amount,
            title: $request->title,
            description: $request->description,
            transactionDate: $request->transactionDate,
        );
        $this->entityManager->wrapInTransaction(function () use ($transaction): void {
            $this->walletBalanceService->applyTransaction($transaction);
            $this->transactionRepository->add($transaction);
        });

        return TransactionResponse::fromEntity($transaction);
    }
}
