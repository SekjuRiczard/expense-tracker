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

namespace App\Transaction\Action;

use App\Entity\User;
use App\Transaction\Dto\Request\TransactionFilterRequest;
use App\Transaction\Dto\Response\TransactionListResponse;
use App\Transaction\Factory\TransactionListResponseFactory;
use App\Transaction\Repository\TransactionRepository;

final readonly class ListTransactionsAction
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private TransactionListResponseFactory $responseFactory,
    ) {
    }

    public function execute(
        User $user,
        TransactionFilterRequest $request,
    ): TransactionListResponse {
        return $this->responseFactory->create(
            paginator: $this->transactionRepository->findByUser(
                $user,
                $request,
            ),
            request: $request,
        );
    }
}
