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

namespace App\Tests\Service;

use App\Dto\UserRegistrationRequest;
use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Repository\User\UserRepository;
use App\Repository\User\UserRepositoryInterface;
use App\Service\AuthService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[CoversClass(AuthService::class)]
#[Small]
final class AuthServiceTest extends TestCase
{
    public function test_it_throws_exception_if_user_already_exists(): void
    {
        $dto = new UserRegistrationRequest('testuser', 'test@test.pl', 'password123');
        $userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $userRepositoryMock->expects($this->once())
            ->method('findOneByEmail')
            ->with('test@test.pl')
            ->willReturn(new User('test@test.pl', 'testuser', 'hash'));

        $hasherFactoryMock = $this->createMock(PasswordHasherFactoryInterface::class);
        $authService = new AuthService($userRepositoryMock, $hasherFactoryMock);
        $this->expectException(UserAlreadyExistsException::class);
        $authService->register($dto);
    }
}