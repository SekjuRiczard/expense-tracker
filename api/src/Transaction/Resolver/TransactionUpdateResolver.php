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

namespace App\Transaction\Resolver;

use App\Category\Entity\Category;
use App\Entity\User;
use App\Transaction\Dto\Internal\TransactionUpdateData;
use App\Transaction\Dto\Request\UpdateTransactionRequest;
use App\Transaction\Entity\Transaction;
use App\Transaction\Exception\TransactionException;
use App\Transaction\Provider\TransactionResourceProvider;
use App\Wallet\Entity\Wallet;

final readonly class TransactionUpdateResolver
{
    public function __construct(
        private TransactionResourceProvider $resourceProvider,
    ) {
    }

    public function resolve(Transaction $transaction, UpdateTransactionRequest $request, User $user): TransactionUpdateData
    {
        $this->assertHasChanges($request);

        return new TransactionUpdateData(
            wallet: $this->resolveWallet($request, $transaction, $user),
            category: $this->resolveCategory($request, $transaction, $user),
            type: $request->type ?? $transaction->getType(),
            amount: $request->amount ?? $transaction->getAmount(),
            title: $request->title ?? $transaction->getTitle(),
            description: $this->resolveDescription($request, $transaction),
            transactionDate: $request->transactionDate ?? $transaction->getTransactionDate(),
        );
    }

    private function resolveWallet(UpdateTransactionRequest $request, Transaction $transaction, User $user): Wallet
    {
        if (null === $request->walletId) {
            return $transaction->getWallet();
        }

        return $this->resourceProvider->getWallet($request->walletId, $user);
    }

    private function resolveCategory(UpdateTransactionRequest $request, Transaction $transaction, User $user): Category
    {
        if (null === $request->categoryId) {
            return $transaction->getCategory();
        }

        return $this->resourceProvider->getCategory($request->categoryId, $user);
    }

    /**
     * @throws TransactionException
     */
    private function assertHasChanges(UpdateTransactionRequest $request): void
    {
        if ([] === array_filter(get_object_vars($request), static fn (mixed $val): bool => null !== $val)) {
            throw TransactionException::nothingToUpdate();
        }
    }

    private function resolveDescription(UpdateTransactionRequest $request, Transaction $transaction): ?string
    {
        if (null === $request->description) {
            return $transaction->getDescription();
        }

        return '' === trim($request->description) ? null : $request->description;
    }
}
