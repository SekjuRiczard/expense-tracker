<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pin = null;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(length: 3)]
    private string $defaultCurrency = 'PLN';

    #[ORM\Column(length: 5)]
    private string $language = 'pl';

    #[ORM\Column(length: 50)]
    private string $timezone = 'Europe/Warsaw';

    #[ORM\Column(type: Types::JSON)]
    private array $notificationSettings = [];

    #[ORM\Column]
    private bool $isActive = true;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->notificationSettings = [
            'email' => true,
            'push' => true,
            'sms' => false,
        ];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return array_unique([...$this->roles, UserRole::USER->value]);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function getPin(): ?string
    {
        return $this->pin;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLastLoginAt(): ?DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getNotificationSettings(): array
    {
        return $this->notificationSettings;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function setRoles(UserRole $role): void
    {
        $this->roles = array_values(array_unique([...$this->roles, $role->value]));
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setAvatarUrl(?string $avatarUrl): void
    {
        $this->avatarUrl = $avatarUrl;
    }

    public function setPin(?string $pin): void
    {
        $this->pin = $pin;
    }

    public function setLastLoginAt(?DateTimeImmutable $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
    }

    public function setDefaultCurrency(string $defaultCurrency): void
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function setTimezone(string $timezone): void
    {
        $this->timezone = $timezone;
    }

    public function setNotificationSettings(array $notificationSettings): void
    {
        $this->notificationSettings = $notificationSettings;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
