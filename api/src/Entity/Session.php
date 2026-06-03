<?php

/**
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Entity;

use App\Enum\SessionStatus;
use App\Session\Repository\SessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: '`session`')]
class Session
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 255, unique: true)]
    private string $tokenHash;

    #[ORM\Column(length: 50)]
    private string $status;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $authenticatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $revokedAt = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $refreshTokenHash = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $refreshTokenExpiresAt = null;

    public function __construct(
        User $user,
        string $tokenHash,
        \DateTimeImmutable $expiresAt,
        SessionStatus $status,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ) {
        $this->user = $user;
        $this->tokenHash = $tokenHash;
        $this->expiresAt = $expiresAt;
        $this->status = $status->value;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getIdAsString(): string
    {
        return (string) $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(string $tokenHash): void
    {
        $this->tokenHash = $tokenHash;
    }

    public function getStatus(): SessionStatus
    {
        return SessionStatus::from($this->status);
    }

    public function setStatus(SessionStatus $status): void
    {
        $this->status = $status->value;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function getAuthenticatedAt(): ?\DateTimeImmutable
    {
        return $this->authenticatedAt;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function markAsAuthenticated(): void
    {
        $this->status = SessionStatus::AUTHENTICATED->value;
        $this->authenticatedAt = new \DateTimeImmutable();
        $this->revokedAt = null;
    }

    public function revoke(): void
    {
        $this->status = SessionStatus::REVOKED->value;
        $this->revokedAt = new \DateTimeImmutable();
        $this->clearRefreshToken();
    }

    public function markAsExpired(): void
    {
        $this->status = SessionStatus::EXPIRED->value;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new \DateTimeImmutable();
    }

    public function isRevoked(): bool
    {
        return SessionStatus::REVOKED === $this->getStatus();
    }

    public function isAuthenticated(): bool
    {
        return SessionStatus::AUTHENTICATED === $this->getStatus();
    }

    public function requiresPinSetup(): bool
    {
        return SessionStatus::PIN_SETUP_REQUIRED === $this->getStatus();
    }

    public function requiresPinVerification(): bool
    {
        return SessionStatus::PIN_VERIFICATION_REQUIRED === $this->getStatus();
    }

    public function getRefreshTokenHash(): ?string
    {
        return $this->refreshTokenHash;
    }

    public function setRefreshTokenHash(?string $refreshTokenHash): void
    {
        $this->refreshTokenHash = $refreshTokenHash;
    }

    public function getRefreshTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->refreshTokenExpiresAt;
    }

    public function setRefreshTokenExpiresAt(?\DateTimeImmutable $refreshTokenExpiresAt): void
    {
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;
    }

    public function hasExpiredRefreshToken(): bool
    {
        return null === $this->refreshTokenExpiresAt
            || $this->refreshTokenExpiresAt <= new \DateTimeImmutable();
    }

    public function clearRefreshToken(): void
    {
        $this->refreshTokenHash = null;
        $this->refreshTokenExpiresAt = null;
    }
}
