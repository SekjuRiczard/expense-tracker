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

namespace App\Budget\Dto\Request;

use App\Budget\Enum\BudgetPeriodType;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateBudgetRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public ?string $name = null,
        #[Assert\Positive]
        public ?int $amount = null,
        public ?CurrencyCode $currency = null,
        public ?BudgetPeriodType $periodType = null,
        public ?DateTimeImmutable $startDate = null,
        public ?DateTimeImmutable $endDate = null,
    ) {
    }
}