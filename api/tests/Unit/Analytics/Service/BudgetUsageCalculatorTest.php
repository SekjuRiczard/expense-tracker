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

use App\Analytics\Repository\BudgetExpenseReaderInterface;
use App\Analytics\Service\BudgetUsageCalculator;
use App\Budget\Entity\Budget;
use App\Budget\Enum\BudgetPeriodType;
use App\Entity\User;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class BudgetUsageCalculatorTest extends TestCase
{
    public function testCalculatesPartiallyUsedBudget(): void
    {
        $user = $this->createUser();

        $budget = $this->createBudget(
            user: $user,
            amount: 300000,
        );

        $expenseReader = $this->createMock(
            BudgetExpenseReaderInterface::class,
        );

        $expenseReader
            ->expects(self::once())
            ->method('sumExpensesForPeriod')
            ->with(
                self::identicalTo($user),
                CurrencyCode::PLN,
                self::equalTo(new DateTimeImmutable('2026-06-01')),
                self::equalTo(new DateTimeImmutable('2026-07-01')),
            )
            ->willReturn(185000);

        $usage = (new BudgetUsageCalculator($expenseReader))
            ->calculate($budget);

        self::assertSame(185000, $usage->spent);
        self::assertSame(115000, $usage->remaining);
        self::assertSame(61.67, $usage->percentage);
        self::assertFalse($usage->exceeded);
    }

    public function testCalculatesUnusedBudget(): void
    {
        $budget = $this->createBudget(
            user: $this->createUser(),
            amount: 300000,
        );

        $expenseReader = $this->createStub(
            BudgetExpenseReaderInterface::class,
        );

        $expenseReader
            ->method('sumExpensesForPeriod')
            ->willReturn(0);

        $usage = (new BudgetUsageCalculator($expenseReader))
            ->calculate($budget);

        self::assertSame(0, $usage->spent);
        self::assertSame(300000, $usage->remaining);
        self::assertSame(0.0, $usage->percentage);
        self::assertFalse($usage->exceeded);
    }

    public function testMarksExceededBudget(): void
    {
        $budget = $this->createBudget(
            user: $this->createUser(),
            amount: 100000,
        );

        $expenseReader = $this->createStub(
            BudgetExpenseReaderInterface::class,
        );

        $expenseReader
            ->method('sumExpensesForPeriod')
            ->willReturn(125000);

        $usage = (new BudgetUsageCalculator($expenseReader))
            ->calculate($budget);

        self::assertSame(125000, $usage->spent);
        self::assertSame(-25000, $usage->remaining);
        self::assertSame(125.0, $usage->percentage);
        self::assertTrue($usage->exceeded);
    }

    public function testDoesNotMarkFullyUsedBudgetAsExceeded(): void
    {
        $budget = $this->createBudget(
            user: $this->createUser(),
            amount: 100000,
        );

        $expenseReader = $this->createStub(
            BudgetExpenseReaderInterface::class,
        );

        $expenseReader
            ->method('sumExpensesForPeriod')
            ->willReturn(100000);

        $usage = (new BudgetUsageCalculator($expenseReader))
            ->calculate($budget);

        self::assertSame(100000, $usage->spent);
        self::assertSame(0, $usage->remaining);
        self::assertSame(100.0, $usage->percentage);
        self::assertFalse($usage->exceeded);
    }

    public function testRejectsBudgetWithNonPositiveAmount(): void
    {
        $budget = $this->createBudget(
            user: $this->createUser(),
            amount: 0,
        );

        $expenseReader = $this->createMock(
            BudgetExpenseReaderInterface::class,
        );

        $expenseReader
            ->expects(self::never())
            ->method('sumExpensesForPeriod');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Budget amount must be greater than zero.',
        );

        (new BudgetUsageCalculator($expenseReader))
            ->calculate($budget);
    }

    private function createUser(): User
    {
        return new User(
            email: 'unit-test@example.com',
            username: 'unit-test',
            password: 'hashed-password',
        );
    }

    private function createBudget(
        User $user,
        int $amount,
    ): Budget {
        return new Budget(
            user: $user,
            name: 'Budżet testowy',
            amount: $amount,
            currency: CurrencyCode::PLN,
            periodType: BudgetPeriodType::MONTHLY,
            startDate: new DateTimeImmutable('2026-06-01'),
            endDate: new DateTimeImmutable('2026-06-30'),
        );
    }
}