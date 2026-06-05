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

final readonly class DashboardResponse
{
    /**
     * @param list<CategoryBreakdownResponse> $categoryBreakdown
     * @param list<CashFlowPointResponse>      $cashFlow
     */
    public function __construct(
        public PeriodSummaryResponse $summary,
        public array $categoryBreakdown,
        public array $cashFlow,
    ) {
    }
}