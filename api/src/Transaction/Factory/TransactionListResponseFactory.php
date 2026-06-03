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

namespace App\Transaction\Factory;

use App\Transaction\Dto\Request\TransactionFilterRequest;
use App\Transaction\Dto\Response\PaginationResponse;
use App\Transaction\Dto\Response\TransactionListResponse;
use App\Transaction\Dto\Response\TransactionResponse;
use App\Transaction\Entity\Transaction;
use Doctrine\ORM\Tools\Pagination\Paginator;

final readonly class TransactionListResponseFactory
{
    /**
     * @param Paginator<Transaction> $paginator
     */
    public function create(
        Paginator $paginator,
        TransactionFilterRequest $request,
    ): TransactionListResponse {
        /** @var list<Transaction> $transactions */
        $transactions = iterator_to_array(
            $paginator->getIterator(),
            false,
        );
        /** @var int $totalItems */
        $totalItems = count($paginator);

        return new TransactionListResponse(
            items: array_map(
                static fn (Transaction $transaction): TransactionResponse => TransactionResponse::fromEntity($transaction),
                $transactions,
            ),
            pagination: new PaginationResponse(
                page: $request->page,
                limit: $request->limit,
                totalItems: $totalItems,
                totalPages: (int) ceil($totalItems / $request->limit),
            ),
        );
    }
}
