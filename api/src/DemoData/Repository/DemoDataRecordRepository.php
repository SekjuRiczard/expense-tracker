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
use App\DemoData\Entity\DemoDataRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DemoDataRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemoDataRecord::class);
    }

    public function add(DemoDataRecord $record): void
    {
        $this->getEntityManager()->persist($record);
    }

    /**
     * @return list<int>
     */
    public function findEntityIds(
        DemoDataBatch $batch,
        string $entityClass,
    ): array {
        /** @var list<array{entityId: int}> $rows */
        $rows = $this->createQueryBuilder('record')
            ->select('record.entityId')
            ->andWhere('record.batch = :batch')
            ->andWhere('record.entityClass = :entityClass')
            ->setParameter('batch', $batch)
            ->setParameter('entityClass', $entityClass)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            static fn (array $row): int => $row['entityId'],
            $rows,
        );
    }

    public function deleteByBatch(DemoDataBatch $batch): void
    {
        $this->createQueryBuilder('record')
            ->delete()
            ->andWhere('record.batch = :batch')
            ->setParameter('batch', $batch)
            ->getQuery()
            ->execute();
    }
}
