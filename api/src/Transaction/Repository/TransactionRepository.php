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

namespace App\Transaction\Repository;

use App\Category\Entity\Category;
use App\Entity\User;
use App\Transaction\Dto\Request\TransactionFilterRequest;
use App\Transaction\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

final class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function add(Transaction $transaction): void
    {
        $this->getEntityManager()->persist($transaction);
    }

    public function remove(Transaction $transaction): void
    {
        $this->getEntityManager()->remove($transaction);
    }

    /**
     * @return Paginator<Transaction>
     */
    public function findByUser(
        User $user,
        TransactionFilterRequest $request,
    ): Paginator {
        $query = $this->createQueryBuilder('t')
            ->addSelect('w', 'c')
            ->join('t.wallet', 'w')
            ->join('t.category', 'c')
            ->andWhere('IDENTITY(t.user) = :userId')
            ->andWhere(':type IS NULL OR t.type = :type')
            ->andWhere(':walletId IS NULL OR IDENTITY(t.wallet) = :walletId')
            ->andWhere(':categoryId IS NULL OR IDENTITY(t.category) = :categoryId')
            ->andWhere(':dateFrom IS NULL OR t.transactionDate >= :dateFrom')
            ->andWhere(':dateTo IS NULL OR t.transactionDate <= :dateTo')
            ->setParameter('userId', $user->getId(), UuidType::NAME)
            ->setParameter('type', $request->type?->value)
            ->setParameter('walletId', $request->walletId)
            ->setParameter('categoryId', $request->categoryId)
            ->setParameter('dateFrom', $request->from, Types::DATETIME_IMMUTABLE)
            ->setParameter('dateTo', $request->to, Types::DATETIME_IMMUTABLE)
            ->orderBy('t.transactionDate', 'DESC')
            ->addOrderBy('t.createdAt', 'DESC')
            ->setFirstResult(($request->page - 1) * $request->limit)
            ->setMaxResults($request->limit)
            ->getQuery();

        return new Paginator($query, fetchJoinCollection: false);
    }

    public function findSingleByUser(int $id, User $user): ?Transaction
    {
        return $this->findOneBy([
            'id' => $id,
            'user' => $user,
        ]);
    }

    public function existsForCategory(Category $category): bool
    {
        return null !== $this->findOneBy(['category' => $category]);
    }
}
