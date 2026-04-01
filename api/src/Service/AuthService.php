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

use App\Dto\UserRegistrationRequest;
use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Repository\User\UserRepository;
use App\Repository\User\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
    ) {
    }

    /**
     * @throws UserAlreadyExistsException
     */
    public function register(UserRegistrationRequest $dto): User
    {
        if ($this->userRepository->findOneByEmail($dto->email)) {
            throw UserAlreadyExistsException::forEmail((string) $dto->email);
        }
        $hasher = $this->hasherFactory->getPasswordHasher(User::class);
        $hashedPassword = $hasher->hash((string) $dto->password);
        $user = new User(
            $dto->email,
            $dto->username,
            $hashedPassword
        );
        $this->userRepository->save($user);

        return $user;
    }
}
