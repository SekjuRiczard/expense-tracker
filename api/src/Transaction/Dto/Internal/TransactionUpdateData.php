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

namespace App\Transaction\Dto\Internal;

use App\Category\Entity\Category;
use App\Transaction\Enum\TransactionType;
use App\Wallet\Entity\Wallet;
use DateTimeImmutable;

final readonly class TransactionUpdateData
{
    public function __construct(
        public Wallet $wallet,
        public Category $category,
        public TransactionType $type,
        public int $amount,
        public string $title,
        public ?string $description,
        public DateTimeImmutable $transactionDate,
    ) {
    }
}
