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

namespace App\Transaction\Dto\Request;

use App\Transaction\Enum\TransactionType;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateTransactionRequest
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Positive]
        public int $walletId,

        #[Assert\NotNull]
        #[Assert\Positive]
        public int $categoryId,

        #[Assert\NotNull]
        public TransactionType $type,

        #[Assert\NotNull]
        #[Assert\Positive]
        public int $amount,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $title,

        #[Assert\NotNull]
        public DateTimeImmutable $transactionDate,

        #[Assert\Length(max: 1000)]
        public ?string $description = null,
    ) {
    }
}
