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
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;
final readonly class UpdateTransactionRequest
{
    public function __construct(
        #[Assert\Positive]
        public ?int $walletId = null,

        #[Assert\Positive]
        public ?int $categoryId = null,

        public ?TransactionType $type = null,

        #[Assert\Positive]
        public ?int $amount = null,

        #[Assert\Length(min: 1, max: 255)]
        public ?string $title = null,

        #[Assert\Length(max: 1000)]
        public ?string $description = null,

        public ?DateTimeImmutable $transactionDate = null,
    ) {
    }
}
