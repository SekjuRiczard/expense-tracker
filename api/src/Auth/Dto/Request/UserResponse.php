<?php

declare(strict_types=1);

namespace App\Auth\Dto\Request;

use App\Entity\User;

final readonly class UserResponse
{
    public function __construct(
        public string $id,
        public string $email,
        public string $username,
        public bool $hasPin,
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            id: (string) $user->getId(),
            email: $user->getEmail(),
            username: $user->getUsername(),
            hasPin: $user->getPin() !== null,
        );
    }

    /**
     * @return array{id: string, email: string, username: string, hasPin: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            'hasPin' => $this->hasPin,
        ];
    }
}
