<?php

/*
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Transaction\Repository;

use App\Category\Entity\Category;
use App\Entity\User;
use App\Transaction\Entity\Transaction;
use App\Wallet\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function save(Transaction $transaction): void
    {
        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();
    }

    public function remove(Transaction $transaction): void
    {
        $this->getEntityManager()->remove($transaction);
        $this->getEntityManager()->flush();
    }

    /**
     * @return list<Transaction>
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user],
            ['transactionDate' => 'DESC', 'createdAt' => 'DESC'],
        );
    }

    public function findSingleByUser(int $id, User $user): ?Transaction
    {
        return $this->findOneBy(['id' => $id, 'user' => $user]);
    }

    public function existsForWallet(Wallet $wallet): bool
    {
        return null !== $this->findOneBy(['wallet' => $wallet]);
    }

    public function existsForCategory(Category $category): bool
    {
        return null !== $this->findOneBy(['category' => $category]);
    }
}
