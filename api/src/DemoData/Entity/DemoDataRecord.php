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

use App\DemoData\Repository\DemoDataRecordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemoDataRecordRepository::class)]
#[ORM\Table(name: 'demo_data_record')]
class DemoDataRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DemoDataBatch::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private DemoDataBatch $batch;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $entityClass;

    #[ORM\Column(type: Types::INTEGER)]
    private int $entityId;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        DemoDataBatch $batch,
        string $entityClass,
        int $entityId,
    ) {
        $this->batch = $batch;
        $this->entityClass = $entityClass;
        $this->entityId = $entityId;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBatch(): DemoDataBatch
    {
        return $this->batch;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
