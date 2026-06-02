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

use App\Transaction\Enum\TransactionType;
use App\Wallet\Entity\Wallet;

final readonly class WalletBalanceService
{
    public function applyTransaction(Wallet $wallet, TransactionType $type, int $amount): void
    {
        match ($type) {
            TransactionType::INCOME => $wallet->increaseBalance($amount),
            TransactionType::EXPENSE => $wallet->decreaseBalance($amount),
        };
    }

    public function revertTransaction(Wallet $wallet, TransactionType $type, int $amount): void
    {
        match ($type) {
            TransactionType::INCOME => $wallet->decreaseBalance($amount),
            TransactionType::EXPENSE => $wallet->increaseBalance($amount),
        };
    }
}
