<?php

declare(strict_types=1);

namespace App\Budget\Resolver;

use App\Budget\Dto\Internal\BudgetUpdateData;
use App\Budget\Dto\Request\UpdateBudgetRequest;
use App\Budget\Entity\Budget;
use App\Budget\Exception\BudgetException;

final readonly class BudgetUpdateResolver
{
    public function resolve(Budget $budget, UpdateBudgetRequest $request): BudgetUpdateData
    {
        $this->assertHasChanges($request);

        return new BudgetUpdateData(
            name: $request->name ?? $budget->getName(),
            amount: $request->amount ?? $budget->getAmount(),
            currency: $request->currency ?? $budget->getCurrency(),
            periodType: $request->periodType ?? $budget->getPeriodType(),
            startDate: $request->startDate ?? $budget->getStartDate(),
            endDate: $request->endDate ?? $budget->getEndDate(),
        );
    }

    private function assertHasChanges(UpdateBudgetRequest $request): void
    {
        if (($request->name ?? $request->amount ?? $request->currency ?? $request->periodType ?? $request->startDate ?? $request->endDate) === null) {
            throw BudgetException::nothingToUpdate();
        }
    }
}