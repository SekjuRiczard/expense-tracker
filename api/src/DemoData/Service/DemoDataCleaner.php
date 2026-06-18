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

namespace App\DemoData\Service;

use App\Budget\Entity\Budget;
use App\DemoData\Dto\Response\ClearDemoDataResponse;
use App\DemoData\Entity\DemoDataBatch;
use App\DemoData\Exception\DemoDataNotFoundException;
use App\DemoData\Repository\DemoDataBatchRepository;
use App\DemoData\Repository\DemoDataRecordRepository;
use App\Entity\User;
use App\Transaction\Entity\Transaction;
use App\Wallet\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DemoDataCleaner
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DemoDataBatchRepository $demoDataBatchRepository,
        private DemoDataRecordRepository $demoDataRecordRepository,
    ) {
    }

    public function clear(User $user): ClearDemoDataResponse
    {
        /** @var DemoDataBatch|null $batch */
        $batch = $this->demoDataBatchRepository->findActiveByUser($user);
        if (null === $batch) {
            throw DemoDataNotFoundException::noActiveBatch();
        }

        return $this->entityManager->wrapInTransaction(
            function () use ($batch): ClearDemoDataResponse {
                /** @var int $transactionsDeleted */
                $transactionsDeleted = $this->deleteRecorded(
                    batch: $batch,
                    entityClass: Transaction::class,
                );
                /** @var int $budgetsDeleted */
                $budgetsDeleted = $this->deleteRecorded(
                    batch: $batch,
                    entityClass: Budget::class,
                );
                /** @var int $walletsDeleted */
                $walletsDeleted = $this->deleteRecorded(
                    batch: $batch,
                    entityClass: Wallet::class,
                );

                $this->demoDataRecordRepository->deleteByBatch($batch);
                $batch->markAsCleared();
                $this->entityManager->flush();

                return new ClearDemoDataResponse(
                    transactionsDeleted: $transactionsDeleted,
                    budgetsDeleted: $budgetsDeleted,
                    walletsDeleted: $walletsDeleted,
                    demoDataExists: false,
                );
            },
        );
    }

    /**
     * @param class-string $entityClass
     */
    private function deleteRecorded(
        DemoDataBatch $batch,
        string $entityClass,
    ): int {
        /** @var list<int> $entityIds */
        $entityIds = $this->demoDataRecordRepository->findEntityIds(
            $batch,
            $entityClass,
        );
        if ([] === $entityIds) {
            return 0;
        }

        return (int) $this->entityManager
            ->createQuery(
                sprintf(
                    'DELETE FROM %s entity WHERE entity.id IN (:ids)',
                    $entityClass,
                ),
            )
            ->setParameter('ids', $entityIds)
            ->execute();
    }
}
