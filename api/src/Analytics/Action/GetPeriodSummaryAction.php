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
use App\Analytics\Dto\Response\PeriodSummaryResponse;
use App\Analytics\Repository\TransactionAnalyticsRepository;
use App\Analytics\Validator\AnalyticsPeriodValidator;
use App\Entity\User;

final readonly class GetPeriodSummaryAction
{
    public function __construct(
        private TransactionAnalyticsRepository $transactionAnalyticsRepository,
        private AnalyticsPeriodValidator $analyticsPeriodValidator,
    ) {
    }

    public function execute(
        AnalyticsPeriodRequest $request,
        User $user,
    ): PeriodSummaryResponse {
        $this->analyticsPeriodValidator->validate(
            $request->from,
            $request->to,
        );

        return PeriodSummaryResponse::create(
            currency: $request->currency,
            from: $request->from,
            to: $request->to,
            summary: $this->transactionAnalyticsRepository->summarizePeriod(
                user: $user,
                currency: $request->currency,
                startDate: $request->from,
                endDateExclusive: $request->to->modify('+1 day'),
            ),
        );
    }
}