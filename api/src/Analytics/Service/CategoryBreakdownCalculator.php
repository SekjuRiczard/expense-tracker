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

namespace App\Analytics\Service;

use App\Analytics\Dto\Internal\CategoryExpenseData;
use App\Analytics\Dto\Response\CategoryBreakdownResponse;

final readonly class CategoryBreakdownCalculator
{
    /**
     * @param list<CategoryExpenseData> $categories
     *
     * @return list<CategoryBreakdownResponse>
     */
    public function calculate(array $categories): array
    {
        $totalAmount = array_sum(
            array_map(
                static fn (CategoryExpenseData $category): int => $category->amount,
                $categories,
            ),
        );

        return array_map(
            static fn (CategoryExpenseData $category): CategoryBreakdownResponse => new CategoryBreakdownResponse(
                categoryId: $category->categoryId,
                categoryName: $category->categoryName,
                amount: $category->amount,
                percentage: self::calculatePercentage(
                    $category->amount,
                    $totalAmount,
                ),
            ),
            $categories,
        );
    }

    private static function calculatePercentage(
        int $amount,
        int $totalAmount,
    ): float {
        return 0 === $totalAmount
            ? 0.0
            : round($amount / $totalAmount * 100, 2);
    }
}