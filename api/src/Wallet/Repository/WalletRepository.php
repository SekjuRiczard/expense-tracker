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

namespace App\Wallet\Repository;

use App\Entity\User;
use App\Wallet\Entity\Wallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

final class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    public function save(Wallet $wallet): void
    {
        $this->getEntityManager()->persist($wallet);
        $this->getEntityManager()->flush();
    }

    public function remove(Wallet $wallet): void
    {
        $this->getEntityManager()->remove($wallet);
        $this->getEntityManager()->flush();
    }

    /**
     * @return list<Wallet>
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC'],
        );
    }

    public function findOneByIdAndUser(int $id, User $user): ?Wallet
    {
        return $this->findOneBy([
            'id' => $id,
            'user' => $user,
        ]);
    }

    private function getUserId(User $user): string
    {
        return $user->getId()?->toRfc4122() ?? throw new LogicException('User ID is required.');
    }
}
