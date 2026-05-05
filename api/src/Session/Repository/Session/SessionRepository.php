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

namespace App\Session\Repository\Session;

use App\Entity\Session;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }
    public function deleteExpiredSessions(DateTimeImmutable $now): int
    {

        return (int) $this->createQueryBuilder('s')->delete()->where('s.expiresAt <= :now')->setParameter('now', $now)->getQuery()->execute();
    }
}