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

namespace App\Budget\Repository;

use App\Budget\Entity\Budget;
use App\Budget\Enum\BudgetPeriodType;
use App\Entity\User;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    public function add(Budget $budget): void
    {
        $this->getEntityManager()->persist($budget);
    }

    public function remove(Budget $budget): void
    {
        $this->getEntityManager()->remove($budget);
    }

    public function findSingleByUser(int $id, User $user): ?Budget
    {
        return $this->findOneBy([
            'id' => $id,
            'user' => $user,
        ]);
    }

    /**
     * @return list<Budget>
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user],
            [
                'startDate' => 'DESC',
                'createdAt' => 'DESC',
            ],
        );
    }

    public function existsForPeriod(
        User $user,
        CurrencyCode $currency,
        BudgetPeriodType $periodType,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ): bool {
        return null !== $this->findOneBy([
                'user' => $user,
                'currency' => $currency,
                'periodType' => $periodType,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
    }

    public function existsForPeriodExceptBudget(
        Budget $budget,
        User $user,
        CurrencyCode $currency,
        BudgetPeriodType $periodType,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ): bool {
        /** @var string $result */
        $result = $this->createQueryBuilder('budget')
            ->select('COUNT(budget.id)')
            ->andWhere('budget.id != :budgetId')
            ->andWhere('budget.user = :user')
            ->andWhere('budget.currency = :currency')
            ->andWhere('budget.periodType = :periodType')
            ->andWhere('budget.startDate = :startDate')
            ->andWhere('budget.endDate = :endDate')
            ->setParameter('budgetId', $budget->getId())
            ->setParameter('user', $user)
            ->setParameter('currency', $currency->value)
            ->setParameter('periodType', $periodType->value)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();

        return 0 < (int) $result;
    }
}