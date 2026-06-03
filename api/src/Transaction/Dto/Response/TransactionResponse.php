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

namespace App\Transaction\Dto\Response;

use App\Transaction\Entity\Transaction;
use LogicException;

final readonly class TransactionResponse
{
    public function __construct(
        public int $id,
        public int $walletId,
        public string $walletName,
        public int $categoryId,
        public string $categoryName,
        public string $type,
        public int $amount,
        public string $currency,
        public string $title,
        public ?string $description,
        public string $transactionDate,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(Transaction $transaction): self
    {
        return new self(
            id: $transaction->getId() ?? throw new LogicException('Transaction ID is required.'),
            walletId: $transaction->getWallet()->getId() ?? throw new LogicException('Wallet ID is required.'),
            walletName: $transaction->getWallet()->getName(),
            categoryId: $transaction->getCategory()->getId() ?? throw new LogicException('Category ID is required.'),
            categoryName: $transaction->getCategory()->getName(),
            type: $transaction->getType()->value,
            amount: $transaction->getAmount(),
            currency: $transaction->getCurrency()->value,
            title: $transaction->getTitle(),
            description: $transaction->getDescription(),
            transactionDate: $transaction->getTransactionDate()->format(DATE_ATOM),
            createdAt: $transaction->getCreatedAt()->format(DATE_ATOM),
            updatedAt: $transaction->getUpdatedAt()->format(DATE_ATOM),
        );
    }
}
