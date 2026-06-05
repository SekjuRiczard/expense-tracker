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
use App\Analytics\Dto\Response\CashFlowPointResponse;
use App\Analytics\Repository\TransactionAnalyticsRepository;
use App\Analytics\Validator\AnalyticsPeriodValidator;
use App\Entity\User;
use App\Analytics\Dto\Internal\CashFlowData;

final readonly class GetCashFlowAction
{
    public function __construct(
        private TransactionAnalyticsRepository $transactionAnalyticsRepository,
        private AnalyticsPeriodValidator $analyticsPeriodValidator,
    ) {
    }

    /**
     * @return list<CashFlowPointResponse>
     */
    public function execute(
        AnalyticsPeriodRequest $request,
        User $user,
    ): array {
        $this->analyticsPeriodValidator->validate(
            $request->from,
            $request->to,
        );

        return array_map(
            static fn (CashFlowData $data): CashFlowPointResponse => CashFlowPointResponse::fromData($data),
            $this->transactionAnalyticsRepository
                ->summarizeCashFlowByMonth(
                    user: $user,
                    currency: $request->currency,
                    startDate: $request->from,
                    endDateExclusive: $request->to->modify('+1 day'),
                ),
        );
    }
}