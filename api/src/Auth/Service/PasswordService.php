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
        match (true) {
            !$this->passwordHasher->isPasswordValid($user, (string) $dto->oldPassword) => throw InvalidPasswordChangeException::invalidCurrentPassword(),
            $dto->newPassword !== $dto->confirmNewPassword => throw InvalidPasswordChangeException::passwordsDoNotMatch(),
            $this->passwordHasher->isPasswordValid($user, (string) $dto->newPassword) => throw InvalidPasswordChangeException::sameAsCurrentPassword(),
            default => null,
        };
        $user->setPassword($this->passwordHasher->hashPassword($user, (string) $dto->newPassword));
        $this->userRepository->save($user);
    }
}
