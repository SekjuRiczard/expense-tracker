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

use App\Analytics\Dto\Internal\CashFlowData;

final readonly class CashFlowPointResponse
{
    public function __construct(
        public string $period,
        public int $income,
        public int $expense,
        public int $balance,
    ) {
    }

    public static function fromData(CashFlowData $data): self
    {
        return new self(
            period: $data->period,
            income: $data->income,
            expense: $data->expense,
            balance: $data->income - $data->expense,
        );
    }
}