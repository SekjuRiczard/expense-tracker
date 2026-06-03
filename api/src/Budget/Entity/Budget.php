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

namespace App\Budget\Entity;

use App\Budget\Enum\BudgetPeriodType;
use App\Budget\Repository\BudgetRepository;
use App\Entity\User;
use App\Wallet\Enum\CurrencyCode;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
#[ORM\Table(name: 'budget')]
#[ORM\UniqueConstraint(
    name: 'unique_user_budget_period',
    columns: [
        'user_id',
        'currency',
        'period_type',
        'start_date',
        'end_date',
    ],
)]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $name;

    #[ORM\Column(type: Types::INTEGER)]
    private int $amount;

    #[ORM\Column(
        type: Types::STRING,
        length: 3,
        enumType: CurrencyCode::class,
    )]
    private CurrencyCode $currency;

    #[ORM\Column(
        type: Types::STRING,
        length: 20,
        enumType: BudgetPeriodType::class,
    )]
    private BudgetPeriodType $periodType;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $endDate;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        User $user,
        string $name,
        int $amount,
        CurrencyCode $currency,
        BudgetPeriodType $periodType,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ) {
        $this->user = $user;
        $this->name = $name;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->periodType = $periodType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function update(
        string $name,
        int $amount,
        CurrencyCode $currency,
        BudgetPeriodType $periodType,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ): void {
        $this->name = $name;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->periodType = $periodType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): CurrencyCode
    {
        return $this->currency;
    }

    public function getPeriodType(): BudgetPeriodType
    {
        return $this->periodType;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}