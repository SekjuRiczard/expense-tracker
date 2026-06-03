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

namespace App\Category\Entity;

use App\Category\Enum\CategoryType;
use App\Category\Repository\CategoryRepository;
use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'category')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $name;

    #[ORM\Column(type: Types::STRING, enumType: CategoryType::class)]
    private CategoryType $type;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isDefault;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        ?User $user,
        string $name,
        CategoryType $type,
        bool $isDefault,
    ) {
        $this->user = $user;
        $this->name = $name;
        $this->type = $type;
        $this->isDefault = $isDefault;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): CategoryType
    {
        return $this->type;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(string $name, CategoryType $type): void
    {
        $this->name = $name;
        $this->type = $type;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
