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
use App\Analytics\Dto\Response\CategoryBreakdownResponse;
use App\Analytics\Repository\TransactionAnalyticsRepository;
use App\Analytics\Service\CategoryBreakdownCalculator;
use App\Analytics\Validator\AnalyticsPeriodValidator;
use App\Entity\User;

final readonly class GetCategoryBreakdownAction
{
    public function __construct(
        private TransactionAnalyticsRepository $transactionAnalyticsRepository,
        private CategoryBreakdownCalculator $categoryBreakdownCalculator,
        private AnalyticsPeriodValidator $analyticsPeriodValidator,
    ) {
    }

    /**
     * @return list<CategoryBreakdownResponse>
     */
    public function execute(
        AnalyticsPeriodRequest $request,
        User $user,
    ): array {
        $this->analyticsPeriodValidator->validate(
            $request->from,
            $request->to,
        );

        return $this->categoryBreakdownCalculator->calculate(
            $this->transactionAnalyticsRepository
                ->sumExpensesGroupedByCategory(
                    user: $user,
                    currency: $request->currency,
                    startDate: $request->from,
                    endDateExclusive: $request->to->modify('+1 day'),
                ),
        );
    }
}