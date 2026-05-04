<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\LoginRequest;
use App\Dto\UserRegistrationRequest;
use App\Entity\User;
use App\Exception\InvalidLoginCredentialsException;
use App\Exception\UserAlreadyExistsException;
use App\Repository\User\UserRepositoryInterface;
use App\Service\AuthService;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Small]
final class AuthServiceTest extends TestCase
{
    #[Test]
    public function register_creates_user_with_hashed_password(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $dto = new UserRegistrationRequest(
            username: 'john',
            email: 'john@example.com',
            password: 'plain-password',
        );

        $repository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('john@example.com')
            ->willReturn(null);

        $passwordHasher
            ->expects(self::once())
            ->method('hashPassword')
            ->with(
                self::isInstanceOf(User::class),
                'plain-password',
            )
            ->willReturn('hashed-password');

        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (User $user): bool {
                return $user->getEmail() === 'john@example.com'
                    && $user->getUsername() === 'john'
                    && $user->getPassword() === 'hashed-password';
            }));

        $service = new AuthService(
            userRepository: $repository,
            passwordHasher: $passwordHasher,
        );

        $user = $service->register($dto);

        self::assertSame('john@example.com', $user->getEmail());
        self::assertSame('john', $user->getUsername());
        self::assertSame('hashed-password', $user->getPassword());
    }

    #[Test]
    public function register_throws_exception_when_email_already_exists(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $existingUser = $this->createUser();

        $dto = new UserRegistrationRequest(
            username: 'john',
            email: 'john@example.com',
            password: 'plain-password',
        );

        $repository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('john@example.com')
            ->willReturn($existingUser);

        $repository
            ->expects(self::never())
            ->method('save');

        $passwordHasher
            ->expects(self::never())
            ->method('hashPassword');

        $service = new AuthService(
            userRepository: $repository,
            passwordHasher: $passwordHasher,
        );

        $this->expectException(UserAlreadyExistsException::class);

        $service->register($dto);
    }

    #[Test]
    public function login_returns_user_when_credentials_are_valid(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $user = $this->createUser();

        $dto = new LoginRequest(
            email: 'john@example.com',
            password: 'plain-password',
        );

        $repository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('john@example.com')
            ->willReturn($user);

        $passwordHasher
            ->expects(self::once())
            ->method('isPasswordValid')
            ->with($user, 'plain-password')
            ->willReturn(true);

        $repository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $service = new AuthService(
            userRepository: $repository,
            passwordHasher: $passwordHasher,
        );

        $loggedUser = $service->login($dto);

        self::assertSame($user, $loggedUser);
        self::assertNotNull($loggedUser->getLastLoginAt());
    }

    #[Test]
    public function login_throws_exception_when_user_does_not_exist(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $dto = new LoginRequest(
            email: 'missing@example.com',
            password: 'plain-password',
        );

        $repository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('missing@example.com')
            ->willReturn(null);

        $passwordHasher
            ->expects(self::never())
            ->method('isPasswordValid');

        $service = new AuthService(
            userRepository: $repository,
            passwordHasher: $passwordHasher,
        );

        $this->expectException(InvalidLoginCredentialsException::class);
        $this->expectExceptionMessage('Invalid email or password.');

        $service->login($dto);
    }

    #[Test]
    public function login_throws_exception_when_password_is_invalid(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $user = $this->createUser();

        $dto = new LoginRequest(
            email: 'john@example.com',
            password: 'wrong-password',
        );

        $repository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('john@example.com')
            ->willReturn($user);

        $passwordHasher
            ->expects(self::once())
            ->method('isPasswordValid')
            ->with($user, 'wrong-password')
            ->willReturn(false);

        $repository
            ->expects(self::never())
            ->method('save');

        $service = new AuthService(
            userRepository: $repository,
            passwordHasher: $passwordHasher,
        );

        $this->expectException(InvalidLoginCredentialsException::class);
        $this->expectExceptionMessage('Invalid email or password.');

        $service->login($dto);
    }

    private function createUser(): User
    {
        return new User(
            email: 'john@example.com',
            username: 'john',
            password: 'hashed-password',
        );
    }
}