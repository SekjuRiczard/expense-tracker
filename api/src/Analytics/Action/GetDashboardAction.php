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

namespace App\Analytics\Action;

use App\Analytics\Dto\Request\AnalyticsPeriodRequest;
use App\Analytics\Dto\Response\DashboardResponse;
use App\Entity\User;

final readonly class GetDashboardAction
{
    public function __construct(
        private GetPeriodSummaryAction $getPeriodSummaryAction,
        private GetCategoryBreakdownAction $getCategoryBreakdownAction,
        private GetCashFlowAction $getCashFlowAction,
    ) {
    }

    public function execute(
        AnalyticsPeriodRequest $request,
        User $user,
    ): DashboardResponse {
        return new DashboardResponse(
            summary: $this->getPeriodSummaryAction->execute(
                $request,
                $user,
            ),
            categoryBreakdown: $this->getCategoryBreakdownAction->execute(
                $request,
                $user,
            ),
            cashFlow: $this->getCashFlowAction->execute(
                $request,
                $user,
            ),
        );
    }
}