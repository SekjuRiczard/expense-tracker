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

namespace App\Service;

use App\Dto\LoginRequest;
use App\Dto\UserRegistrationRequest;
use App\Entity\User;
use App\Exception\InvalidLoginCredentialsException;
use App\Exception\UserAlreadyExistsException;
use App\Repository\User\UserRepositoryInterface;
use DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @throws UserAlreadyExistsException
     */
    public function register(UserRegistrationRequest $dto): User
    {
        if ($this->userRepository->findOneByEmail((string) $dto->email) instanceof User) {
            throw UserAlreadyExistsException::forEmail((string) $dto->email);
        }

        $user = new User(
            email: (string) $dto->email,
            username: (string) $dto->username,
            password: '',
        );

        $hashedPassword = $this->passwordHasher->hashPassword(
            user: $user,
            plainPassword: (string) $dto->password,
        );

        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * @throws InvalidLoginCredentialsException
     */
    public function login(LoginRequest $dto): User
    {
        $user = $this->userRepository->findOneByEmail((string) $dto->email);

        if (!$user instanceof User) {
            throw InvalidLoginCredentialsException::create();
        }

        if (!$user->isActive()) {
            throw InvalidLoginCredentialsException::create();
        }

        if (!$this->passwordHasher->isPasswordValid($user, (string) $dto->password)) {
            throw InvalidLoginCredentialsException::create();
        }

        $user->setLastLoginAt(new DateTimeImmutable());
        $this->userRepository->save($user);

        return $user;
    }
}
