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

namespace App\Transaction\Dto\Request;

use App\Transaction\Enum\TransactionType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final readonly class TransactionFilterRequest
{
    public function __construct(
        #[Assert\Positive]
        public int $page = 1,
        #[Assert\Range(min: 1, max: 100)]
        public int $limit = 20,
        public ?TransactionType $type = null,
        #[Assert\Positive]
        public ?int $walletId = null,
        #[Assert\Positive]
        public ?int $categoryId = null,
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
    ) {
    }

    /**
     * @used-by Symfony Validator
     */
    #[Assert\Callback]
    public function validateDateRange(ExecutionContextInterface $context): void
    {
        if (
            null !== $this->from
            && null !== $this->to
            && $this->from > $this->to
        ) {
            $context
                ->buildViolation('The start date must be earlier than or equal to the end date.')
                ->atPath('from')
                ->addViolation();
        }
    }
}
