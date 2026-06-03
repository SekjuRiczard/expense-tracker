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

namespace App\Wallet\Dto\Response;

use App\Wallet\Entity\Wallet;

final readonly class WalletResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public string $currency,
        public int $balanceAmount,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(Wallet $wallet): self
    {
        return new self(
            id: (int) $wallet->getId(),
            name: $wallet->getName(),
            type: $wallet->getType()->value,
            currency: $wallet->getCurrency()->value,
            balanceAmount: $wallet->getBalanceAmount(),
            createdAt: $wallet->getCreatedAt()->format(DATE_ATOM),
            updatedAt: $wallet->getUpdatedAt()->format(DATE_ATOM),
        );
    }
}
