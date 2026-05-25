<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Auth\Dto\Request\ChangePasswordRequest;
use App\Auth\Repository\UserRepositoryInterface;
use App\Entity\User;
use App\Shared\Exception\InvalidPasswordChangeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class PasswordService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function changePassword(User $user, ChangePasswordRequest $dto): void
    {
        if (!$this->passwordHasher->isPasswordValid($user, (string) $dto->oldPassword)) {
            throw InvalidPasswordChangeException::invalidCurrentPassword();
        }

        if ($dto->newPassword !== $dto->confirmNewPassword) {
            throw InvalidPasswordChangeException::passwordsDoNotMatch();
        }

        if ($this->passwordHasher->isPasswordValid($user, (string) $dto->newPassword)) {
            throw InvalidPasswordChangeException::sameAsCurrentPassword();
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, (string) $dto->newPassword)
        );

        $this->userRepository->save($user);
    }
}