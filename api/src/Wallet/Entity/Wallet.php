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

namespace App\Wallet\Entity;

use App\Entity\User;
use App\Wallet\Enum\CurrencyCode;
use App\Wallet\Enum\WalletType;
use App\Wallet\Repository\WalletRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
#[ORM\Table(name: '`wallet`')]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::STRING, enumType: WalletType::class)]
    private WalletType $type;

    #[ORM\Column(type: Types::STRING, length: 3, enumType: CurrencyCode::class)]
    private CurrencyCode $currency;

    #[ORM\Column(type: Types::INTEGER)]
    private int $balanceAmount;

    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    private int $version = 1;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        User $user,
        string $name,
        WalletType $type,
        CurrencyCode $currency,
        int $balanceAmount,
    ) {
        $this->user = $user;
        $this->name = $name;
        $this->type = $type;
        $this->currency = $currency;
        $this->balanceAmount = $balanceAmount;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): WalletType
    {
        return $this->type;
    }

    public function getCurrency(): CurrencyCode
    {
        return $this->currency;
    }

    public function getBalanceAmount(): int
    {
        return $this->balanceAmount;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(string $name, WalletType $type): void
    {
        $this->name = $name;
        $this->type = $type;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function increaseBalance(int $amount): void
    {
        $this->balanceAmount += $amount;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function decreaseBalance(int $amount): void
    {
        $this->balanceAmount -= $amount;
        $this->updatedAt = new DateTimeImmutable();
    }
}
