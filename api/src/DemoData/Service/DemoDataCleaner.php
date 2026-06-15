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
use App\Entity\User;
use App\Transaction\Entity\Transaction;
use App\Wallet\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

final readonly class DemoDataCleaner
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function clear(User $user): ClearDemoDataResponse
    {
        return $this->entityManager->wrapInTransaction(
            function () use ($user): ClearDemoDataResponse {
                /** @var int $transactionsDeleted */
                $transactionsDeleted = $this->deleteForUser(
                    entityClass: Transaction::class,
                    user: $user,
                );
                /** @var int $budgetsDeleted */
                $budgetsDeleted = $this->deleteForUser(
                    entityClass: Budget::class,
                    user: $user,
                );
                /** @var int $walletsDeleted */
                $walletsDeleted = $this->deleteForUser(
                    entityClass: Wallet::class,
                    user: $user,
                );

                return new ClearDemoDataResponse(
                    transactionsDeleted: $transactionsDeleted,
                    budgetsDeleted: $budgetsDeleted,
                    walletsDeleted: $walletsDeleted,
                );
            },
        );
    }

    /**
     * @param class-string $entityClass
     */
    private function deleteForUser(
        string $entityClass,
        User $user,
    ): int {
        /** @var Uuid $userId */
        $userId = $user->getId()
            ?? throw new LogicException('User ID is required.');

        return $this->entityManager
            ->createQuery(
                sprintf(
                    'DELETE FROM %s entity WHERE IDENTITY(entity.user) = :userId',
                    $entityClass,
                ),
            )
            ->setParameter(
                key: 'userId',
                value: $userId,
                type: UuidType::NAME,
            )
            ->execute();
    }
}
