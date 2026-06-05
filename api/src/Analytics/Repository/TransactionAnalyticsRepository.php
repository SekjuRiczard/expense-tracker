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

namespace App\Analytics\Repository;

use App\Analytics\Dto\Internal\CashFlowData;
use App\Analytics\Dto\Internal\CategoryExpenseData;
use App\Entity\User;
use App\Transaction\Entity\Transaction;
use App\Transaction\Enum\TransactionType;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use App\Analytics\Dto\Internal\PeriodSummaryData;

final class TransactionAnalyticsRepository extends ServiceEntityRepository implements BudgetExpenseReaderInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $connection,
    ) {
        parent::__construct($registry, Transaction::class);
    }

    public function sumExpensesForPeriod(
        User $user,
        CurrencyCode $currency,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDateExclusive,
    ): int {
        /** @var string|null $result */
        $result = $this->createQueryBuilder('transaction')
            ->select('SUM(transaction.amount)')
            ->join('transaction.wallet', 'wallet')
            ->andWhere('IDENTITY(transaction.user) = :userId')
            ->andWhere('transaction.type = :type')
            ->andWhere('wallet.currency = :currency')
            ->andWhere('transaction.transactionDate >= :startDate')
            ->andWhere('transaction.transactionDate < :endDateExclusive')
            ->setParameter('userId', $user->getId(), UuidType::NAME)
            ->setParameter('type', TransactionType::EXPENSE->value)
            ->setParameter('currency', $currency->value)
            ->setParameter(
                'startDate',
                $startDate,
                Types::DATETIME_IMMUTABLE,
            )
            ->setParameter(
                'endDateExclusive',
                $endDateExclusive,
                Types::DATETIME_IMMUTABLE,
            )
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    public function summarizePeriod(
        User $user,
        CurrencyCode $currency,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDateExclusive,
    ): PeriodSummaryData {
        /** @var array{
         *     income: string,
         *     expense: string,
         *     transactionCount: string
         * } $result
         */
        $result = $this->createQueryBuilder('transaction')
            ->select(
                'COALESCE(SUM(
                CASE
                    WHEN transaction.type = :incomeType
                    THEN transaction.amount
                    ELSE 0
                END
            ), 0) AS income',
            )
            ->addSelect(
                'COALESCE(SUM(
                CASE
                    WHEN transaction.type = :expenseType
                    THEN transaction.amount
                    ELSE 0
                END
            ), 0) AS expense',
            )
            ->addSelect('COUNT(transaction.id) AS transactionCount')
            ->join('transaction.wallet', 'wallet')
            ->andWhere('IDENTITY(transaction.user) = :userId')
            ->andWhere('wallet.currency = :currency')
            ->andWhere('transaction.transactionDate >= :startDate')
            ->andWhere('transaction.transactionDate < :endDateExclusive')
            ->setParameter('userId', $user->getId(), UuidType::NAME)
            ->setParameter('currency', $currency->value)
            ->setParameter('incomeType', TransactionType::INCOME->value)
            ->setParameter('expenseType', TransactionType::EXPENSE->value)
            ->setParameter(
                'startDate',
                $startDate,
                Types::DATETIME_IMMUTABLE,
            )
            ->setParameter(
                'endDateExclusive',
                $endDateExclusive,
                Types::DATETIME_IMMUTABLE,
            )
            ->getQuery()
            ->getSingleResult();

        return new PeriodSummaryData(
            income: (int) $result['income'],
            expense: (int) $result['expense'],
            transactionCount: (int) $result['transactionCount'],
        );
    }

    /**
     * @return list<CategoryExpenseData>
     */
    public function sumExpensesGroupedByCategory(
        User $user,
        CurrencyCode $currency,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDateExclusive,
    ): array {
        /**
         * @var list<array{
         *     categoryId: int|string,
         *     categoryName: string,
         *     amount: int|string
         * }> $results
         */
        $results = $this->createQueryBuilder('transaction')
            ->select('IDENTITY(transaction.category) AS categoryId')
            ->addSelect('category.name AS categoryName')
            ->addSelect('SUM(transaction.amount) AS amount')
            ->join('transaction.wallet', 'wallet')
            ->join('transaction.category', 'category')
            ->andWhere('IDENTITY(transaction.user) = :userId')
            ->andWhere('transaction.type = :type')
            ->andWhere('wallet.currency = :currency')
            ->andWhere('transaction.transactionDate >= :startDate')
            ->andWhere('transaction.transactionDate < :endDateExclusive')
            ->setParameter('userId', $user->getId(), UuidType::NAME)
            ->setParameter('type', TransactionType::EXPENSE->value)
            ->setParameter('currency', $currency->value)
            ->setParameter(
                'startDate',
                $startDate,
                Types::DATETIME_IMMUTABLE,
            )
            ->setParameter(
                'endDateExclusive',
                $endDateExclusive,
                Types::DATETIME_IMMUTABLE,
            )
            ->groupBy('category.id')
            ->addGroupBy('category.name')
            ->orderBy('amount', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $result): CategoryExpenseData => new CategoryExpenseData(
                categoryId: (int) $result['categoryId'],
                categoryName: $result['categoryName'],
                amount: (int) $result['amount'],
            ),
            $results,
        );
    }

    /**
     * @return list<CashFlowData>
     */
    public function summarizeCashFlowByMonth(
        User $user,
        CurrencyCode $currency,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDateExclusive,
    ): array {
        /**
         * @var list<array{
         *     period: string,
         *     income: int|string,
         *     expense: int|string
         * }> $results
         */
        $results = $this->connection
            ->createQueryBuilder()
            ->select(
                "DATE_FORMAT(transaction.transaction_date, '%Y-%m') AS period",
            )
            ->addSelect(
                'COALESCE(SUM(
                CASE
                    WHEN transaction.type = :incomeType
                    THEN transaction.amount
                    ELSE 0
                END
            ), 0) AS income',
            )
            ->addSelect(
                'COALESCE(SUM(
                CASE
                    WHEN transaction.type = :expenseType
                    THEN transaction.amount
                    ELSE 0
                END
            ), 0) AS expense',
            )
            ->from('`transaction`', 'transaction')
            ->innerJoin(
                'transaction',
                'wallet',
                'wallet',
                'wallet.id = transaction.wallet_id',
            )
            ->andWhere('transaction.user_id = :userId')
            ->andWhere('wallet.currency = :currency')
            ->andWhere('transaction.transaction_date >= :startDate')
            ->andWhere('transaction.transaction_date < :endDateExclusive')
            ->setParameter('userId', $user->getId(), UuidType::NAME)
            ->setParameter('currency', $currency->value)
            ->setParameter('incomeType', TransactionType::INCOME->value)
            ->setParameter('expenseType', TransactionType::EXPENSE->value)
            ->setParameter(
                'startDate',
                $startDate,
                Types::DATETIME_IMMUTABLE,
            )
            ->setParameter(
                'endDateExclusive',
                $endDateExclusive,
                Types::DATETIME_IMMUTABLE,
            )
            ->groupBy(
                "DATE_FORMAT(transaction.transaction_date, '%Y-%m')",
            )
            ->orderBy('period', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(
            static fn (array $result): CashFlowData => new CashFlowData(
                period: $result['period'],
                income: (int) $result['income'],
                expense: (int) $result['expense'],
            ),
            $results,
        );
    }
}