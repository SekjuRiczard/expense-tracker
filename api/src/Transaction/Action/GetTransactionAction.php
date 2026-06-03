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
use App\Transaction\Dto\Response\TransactionResponse;
use App\Transaction\Provider\TransactionResourceProvider;

final readonly class GetTransactionAction
{
    public function __construct(
        private TransactionResourceProvider $resourceProvider,
    ) {
    }

    public function execute(int $id, User $user): TransactionResponse
    {
        return TransactionResponse::fromEntity(
            $this->resourceProvider->getTransaction($id, $user),
        );
    }
}
