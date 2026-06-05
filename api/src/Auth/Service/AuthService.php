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

use App\Auth\Dto\Request\LoginRequest;
use App\Auth\Dto\Request\UserRegistrationRequest;
use App\Auth\Repository\UserRepositoryInterface;
use App\Entity\User;
use App\Shared\Exception\InvalidLoginCredentialsException;
use App\Shared\Exception\UserAlreadyExistsException;
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
        /** @var User $user */
        $user = new User(
            email: (string) $dto->email,
            username: (string) $dto->username,
            password: '',
        );
        /** @var string $hashedPassword */
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
        /** @var User $user */
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
