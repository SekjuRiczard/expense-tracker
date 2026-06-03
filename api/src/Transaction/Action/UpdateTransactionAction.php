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
use App\Transaction\Dto\Internal\TransactionUpdateData;
use App\Transaction\Dto\Request\UpdateTransactionRequest;
use App\Transaction\Dto\Response\TransactionResponse;
use App\Transaction\Entity\Transaction;
use App\Transaction\Provider\TransactionResourceProvider;
use App\Transaction\Resolver\TransactionUpdateResolver;
use App\Transaction\Service\TransactionUpdateApplier;
use App\Transaction\Validator\TransactionValidator;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UpdateTransactionAction
{
    public function __construct(
        private TransactionResourceProvider $resourceProvider,
        private TransactionUpdateResolver $updateResolver,
        private TransactionValidator $transactionValidator,
        private TransactionUpdateApplier $updateApplier,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(
        int $id,
        UpdateTransactionRequest $request,
        User $user,
    ): TransactionResponse {
        /** @var Transaction $transaction */
        $transaction = $this->resourceProvider->getTransaction($id, $user);
        /** @var TransactionUpdateData $data */
        $data = $this->updateResolver->resolve(
            transaction: $transaction,
            request: $request,
            user: $user,
        );
        $this->transactionValidator->validateCategoryType(
            category: $data->category,
            transactionType: $data->type,
        );
        $this->entityManager->wrapInTransaction(
            fn (): null => $this->applyUpdate($transaction, $data),
        );

        return TransactionResponse::fromEntity($transaction);
    }

    private function applyUpdate(
        Transaction $transaction,
        TransactionUpdateData $data,
    ): null {
        $this->updateApplier->apply($transaction, $data);

        return null;
    }
}
