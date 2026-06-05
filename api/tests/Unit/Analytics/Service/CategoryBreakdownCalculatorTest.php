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

namespace App\Tests\Unit\Analytics\Service;

use App\Analytics\Dto\Internal\CategoryExpenseData;
use App\Analytics\Service\CategoryBreakdownCalculator;
use PHPUnit\Framework\TestCase;

final class CategoryBreakdownCalculatorTest extends TestCase
{
    public function testReturnsEmptyListForEmptyInput(): void
    {
        $calculator = new CategoryBreakdownCalculator();

        self::assertSame([], $calculator->calculate([]));
    }

    public function testCalculatesCategoryPercentages(): void
    {
        $calculator = new CategoryBreakdownCalculator();

        $result = $calculator->calculate([
            new CategoryExpenseData(
                categoryId: 1,
                categoryName: 'Jedzenie',
                amount: 85000,
            ),
            new CategoryExpenseData(
                categoryId: 2,
                categoryName: 'Transport',
                amount: 35000,
            ),
        ]);

        self::assertCount(2, $result);

        self::assertSame(1, $result[0]->categoryId);
        self::assertSame('Jedzenie', $result[0]->categoryName);
        self::assertSame(85000, $result[0]->amount);
        self::assertSame(70.83, $result[0]->percentage);

        self::assertSame(2, $result[1]->categoryId);
        self::assertSame('Transport', $result[1]->categoryName);
        self::assertSame(35000, $result[1]->amount);
        self::assertSame(29.17, $result[1]->percentage);
    }

    public function testCalculatesOneHundredPercentForSingleCategory(): void
    {
        $calculator = new CategoryBreakdownCalculator();

        $result = $calculator->calculate([
            new CategoryExpenseData(
                categoryId: 1,
                categoryName: 'Jedzenie',
                amount: 50000,
            ),
        ]);

        self::assertCount(1, $result);
        self::assertSame(100.0, $result[0]->percentage);
    }

    public function testPreservesInputOrder(): void
    {
        $calculator = new CategoryBreakdownCalculator();

        $result = $calculator->calculate([
            new CategoryExpenseData(
                categoryId: 2,
                categoryName: 'Transport',
                amount: 35000,
            ),
            new CategoryExpenseData(
                categoryId: 1,
                categoryName: 'Jedzenie',
                amount: 85000,
            ),
        ]);

        self::assertSame('Transport', $result[0]->categoryName);
        self::assertSame('Jedzenie', $result[1]->categoryName);
    }

    public function testReturnsZeroPercentageWhenTotalAmountIsZero(): void
    {
        $calculator = new CategoryBreakdownCalculator();

        $result = $calculator->calculate([
            new CategoryExpenseData(
                categoryId: 1,
                categoryName: 'Pusta kategoria',
                amount: 0,
            ),
        ]);

        self::assertCount(1, $result);
        self::assertSame(0.0, $result[0]->percentage);
    }
}