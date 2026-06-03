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

namespace App\Transaction\Service;

use App\Transaction\Dto\Internal\TransactionUpdateData;
use App\Transaction\Entity\Transaction;

final readonly class TransactionUpdateApplier
{
    public function __construct(
        private WalletBalanceService $walletBalanceService,
    ) {
    }

    public function apply(
        Transaction $transaction,
        TransactionUpdateData $data,
    ): void {
        $this->walletBalanceService->revertTransaction($transaction);
        $transaction->update(
            wallet: $data->wallet,
            category: $data->category,
            type: $data->type,
            amount: $data->amount,
            title: $data->title,
            description: $data->description,
            transactionDate: $data->transactionDate,
        );
        $this->walletBalanceService->applyTransaction($transaction);
    }
}
