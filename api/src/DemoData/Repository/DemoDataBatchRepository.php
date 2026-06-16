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

namespace App\DemoData\Repository;

use App\DemoData\Entity\DemoDataBatch;
use App\DemoData\Enum\DemoDataBatchStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DemoDataBatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemoDataBatch::class);
    }

    public function add(DemoDataBatch $batch): void
    {
        $this->getEntityManager()->persist($batch);
    }

    public function findActiveByUser(User $user): ?DemoDataBatch
    {
        return $this->findOneBy([
            'user' => $user,
            'status' => DemoDataBatchStatus::ACTIVE,
        ]);
    }
}
