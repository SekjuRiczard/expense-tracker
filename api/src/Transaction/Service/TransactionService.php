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

namespace App\Transaction\Service;

use App\Category\Entity\Category;
use App\Category\Repository\CategoryRepository;
use App\Entity\User;
use App\Transaction\Dto\Request\CreateTransactionRequest;
use App\Transaction\Dto\Request\UpdateTransactionRequest;
use App\Transaction\Dto\Response\TransactionResponse;
use App\Transaction\Entity\Transaction;
use App\Transaction\Enum\TransactionType;
use App\Transaction\Exception\TransactionException;
use App\Transaction\Repository\TransactionRepository;
use App\Wallet\Entity\Wallet;
use App\Wallet\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransactionService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private WalletRepository $walletRepository,
        private CategoryRepository $categoryRepository,
        private WalletBalanceService $walletBalanceService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createTransaction(CreateTransactionRequest $request, User $user): TransactionResponse
    {
        /** @var Wallet $wallet */
        $wallet = $this->walletRepository->findOneByIdAndUser($request->walletId, $user)
            ?? throw TransactionException::walletNotFound();

        /** @var Category $category */
        $category = $this->categoryRepository->findSingleCategory($request->categoryId, $user)
            ?? throw TransactionException::categoryNotFound();

        if ($request->type->value !== $category->getType()->value) {
            throw TransactionException::categoryTypeMismatch();
        }

        /** @var Transaction $transaction */
        $transaction = new Transaction(
            user: $user,
            wallet: $wallet,
            category: $category,
            type: $request->type,
            amount: $request->amount,
            title: $request->title,
            description: $request->description,
            transactionDate: $request->transactionDate,
        );

        $this->entityManager->wrapInTransaction(function () use ($transaction, $wallet, $request): void {
            $this->walletBalanceService->applyTransaction($wallet, $request->type, $request->amount);
            $this->transactionRepository->save($transaction);
        });
        return TransactionResponse::fromEntity($transaction);
    }

    /**
     * @return list<TransactionResponse>
     */
    public function getTransactions(User $user): array
    {
        return array_map(
            static fn (Transaction $transaction): TransactionResponse => TransactionResponse::fromEntity($transaction),
            $this->transactionRepository->findByUser($user),
        );
    }

    public function getTransaction(int $id, User $user): TransactionResponse
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->findSingleByUser($id, $user)
            ?? throw TransactionException::notFound();
        return TransactionResponse::fromEntity($transaction);
    }

    public function updateTransaction(int $id, UpdateTransactionRequest $request, User $user): TransactionResponse
    {
        if (
            null === $request->walletId
            && null === $request->categoryId
            && null === $request->type
            && null === $request->amount
            && null === $request->title
            && null === $request->description
            && null === $request->transactionDate
        ) {
            throw TransactionException::nothingToUpdate();
        }

        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->findSingleByUser($id, $user)
            ?? throw TransactionException::notFound();

        /** @var Wallet $newWallet */
        $newWallet = null !== $request->walletId
            ? ($this->walletRepository->findOneByIdAndUser($request->walletId, $user)
                ?? throw TransactionException::walletNotFound())
            : $transaction->getWallet();

        /** @var Category $newCategory */
        $newCategory = null !== $request->categoryId
            ? ($this->categoryRepository->findSingleCategory($request->categoryId, $user)
                ?? throw TransactionException::categoryNotFound())
            : $transaction->getCategory();

        /** @var TransactionType $newType */
        $newType = $request->type ?? $transaction->getType();
        /** @var int $newAmount */
        $newAmount = $request->amount ?? $transaction->getAmount();

        if ($newType->value !== $newCategory->getType()->value) {
            throw TransactionException::categoryTypeMismatch();
        }

        /** @var Wallet $oldWallet */
        $oldWallet = $transaction->getWallet();
        /** @var TransactionType $oldType */
        $oldType = $transaction->getType();
        /** @var int $oldAmount */
        $oldAmount = $transaction->getAmount();

        $this->entityManager->wrapInTransaction(function () use (
            $transaction,
            $oldWallet,
            $oldType,
            $oldAmount,
            $newWallet,
            $newCategory,
            $newType,
            $newAmount,
            $request,
        ): void {
            $this->walletBalanceService->revertTransaction($oldWallet, $oldType, $oldAmount);
            $transaction->update(
                wallet: $newWallet,
                category: $newCategory,
                type: $newType,
                amount: $newAmount,
                title: $request->title ?? $transaction->getTitle(),
                description: $request->description ?? $transaction->getDescription(),
                transactionDate: $request->transactionDate ?? $transaction->getTransactionDate(),
            );
            $this->walletBalanceService->applyTransaction($newWallet, $newType, $newAmount);
            $this->transactionRepository->save($transaction);
        });
        return TransactionResponse::fromEntity($transaction);
    }

    public function deleteTransaction(int $id, User $user): void
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->findSingleByUser($id, $user)
            ?? throw TransactionException::notFound();

        $this->entityManager->wrapInTransaction(function () use ($transaction): void {
            $this->walletBalanceService->revertTransaction(
                $transaction->getWallet(),
                $transaction->getType(),
                $transaction->getAmount(),
            );
            $this->transactionRepository->remove($transaction);
        });
    }
}
