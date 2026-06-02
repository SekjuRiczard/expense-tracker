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

namespace App\Transaction\Entity;

use App\Category\Entity\Category;
use App\Entity\User;
use App\Transaction\Enum\TransactionType;
use App\Transaction\Repository\TransactionRepository;
use App\Wallet\Entity\Wallet;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: '`transaction`')]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Wallet::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Wallet $wallet;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Category $category;

    #[ORM\Column(type: Types::STRING, enumType: TransactionType::class)]
    private TransactionType $type;

    #[ORM\Column(type: Types::INTEGER)]
    private int $amount;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
    private ?string $description;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $transactionDate;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        User $user,
        Wallet $wallet,
        Category $category,
        TransactionType $type,
        int $amount,
        string $title,
        ?string $description,
        DateTimeImmutable $transactionDate,
    ) {
        $this->user = $user;
        $this->wallet = $wallet;
        $this->category = $category;
        $this->type = $type;
        $this->amount = $amount;
        $this->title = $title;
        $this->description = $description;
        $this->transactionDate = $transactionDate;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): CurrencyCode
    {
        return $this->wallet->getCurrency();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTransactionDate(): DateTimeImmutable
    {
        return $this->transactionDate;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(
        Wallet $wallet,
        Category $category,
        TransactionType $type,
        int $amount,
        string $title,
        ?string $description,
        DateTimeImmutable $transactionDate,
    ): void {
        $this->wallet = $wallet;
        $this->category = $category;
        $this->type = $type;
        $this->amount = $amount;
        $this->title = $title;
        $this->description = $description;
        $this->transactionDate = $transactionDate;
        $this->updatedAt = new DateTimeImmutable();
    }
}
