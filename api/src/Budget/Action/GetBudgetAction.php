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

namespace App\Budget\Action;

use App\Budget\Dto\Response\BudgetResponse;
use App\Budget\Provider\BudgetResourceProvider;
use App\Entity\User;

final readonly class GetBudgetAction
{
    public function __construct(
        private BudgetResourceProvider $resourceProvider,
    ) {
    }

    public function execute(int $id, User $user): BudgetResponse
    {
        return BudgetResponse::fromEntity(
            $this->resourceProvider->getBudget($id, $user),
        );
    }
}