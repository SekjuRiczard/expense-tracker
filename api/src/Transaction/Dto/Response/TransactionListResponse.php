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

namespace App\Transaction\Dto\Response;

final readonly class TransactionListResponse
{
    /**
     * @param list<TransactionResponse> $items
     */
    public function __construct(
        public array $items,
        public PaginationResponse $pagination,
    ) {
    }
}
