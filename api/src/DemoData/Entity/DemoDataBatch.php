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

namespace App\DemoData\Entity;

use App\DemoData\Enum\DemoDataBatchStatus;
use App\DemoData\Repository\DemoDataBatchRepository;
use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemoDataBatchRepository::class)]
#[ORM\Table(name: 'demo_data_batch')]
class DemoDataBatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::INTEGER)]
    private int $seed;

    #[ORM\Column(
        type: Types::STRING,
        length: 20,
        enumType: DemoDataBatchStatus::class,
    )]
    private DemoDataBatchStatus $status;

    #[ORM\Column(type: Types::INTEGER)]
    private int $walletsCount;

    #[ORM\Column(type: Types::INTEGER)]
    private int $budgetsCount;

    #[ORM\Column(type: Types::INTEGER)]
    private int $transactionsCount;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $generatedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $clearedAt = null;

    public function __construct(
        User $user,
        int $seed,
        int $walletsCount,
        int $budgetsCount,
        int $transactionsCount,
    ) {
        $this->user = $user;
        $this->seed = $seed;
        $this->walletsCount = $walletsCount;
        $this->budgetsCount = $budgetsCount;
        $this->transactionsCount = $transactionsCount;
        $this->status = DemoDataBatchStatus::ACTIVE;
        $this->generatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSeed(): int
    {
        return $this->seed;
    }

    public function getStatus(): DemoDataBatchStatus
    {
        return $this->status;
    }

    public function getWalletsCount(): int
    {
        return $this->walletsCount;
    }

    public function getBudgetsCount(): int
    {
        return $this->budgetsCount;
    }

    public function getTransactionsCount(): int
    {
        return $this->transactionsCount;
    }

    public function getGeneratedAt(): \DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function getClearedAt(): ?\DateTimeImmutable
    {
        return $this->clearedAt;
    }

    public function markAsCleared(): void
    {
        $this->status = DemoDataBatchStatus::CLEARED;
        $this->clearedAt = new \DateTimeImmutable();
    }
}
