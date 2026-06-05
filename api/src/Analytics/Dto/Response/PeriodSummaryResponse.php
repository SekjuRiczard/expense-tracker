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

namespace App\Analytics\Dto\Response;

use App\Analytics\Dto\Internal\PeriodSummaryData;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;

final readonly class PeriodSummaryResponse
{
    public function __construct(
        public string $currency,
        public string $from,
        public string $to,
        public int $income,
        public int $expense,
        public int $balance,
        public int $transactionCount,
    ) {
    }

    public static function create(
        CurrencyCode $currency,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        PeriodSummaryData $summary,
    ): self {
        return new self(
            currency: $currency->value,
            from: $from->format('Y-m-d'),
            to: $to->format('Y-m-d'),
            income: $summary->income,
            expense: $summary->expense,
            balance: $summary->income - $summary->expense,
            transactionCount: $summary->transactionCount,
        );
    }
}