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

namespace App\Tests\Unit\Auth\Service;

use App\Auth\Dto\Request\ChangePasswordRequest;
use App\Auth\Repository\UserRepositoryInterface;
use App\Auth\Service\PasswordService;
use App\Entity\User;
use App\Shared\Exception\InvalidPasswordChangeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class PasswordServiceTest extends TestCase
{
    private UserPasswordHasherInterface $passwordHasher;

    private UserRepositoryInterface $userRepository;

    private PasswordService $passwordService;

    protected function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->passwordService = new PasswordService(
            passwordHasher: $this->passwordHasher,
            userRepository: $this->userRepository,
        );
    }

    public function testChangePasswordHashesAndSavesNewPassword(): void
    {
        $user = $this->createUser();

        $dto = new ChangePasswordRequest(
            oldPassword: 'OldPassword123!',
            newPassword: 'NewPassword123!',
            confirmNewPassword: 'NewPassword123!',
        );

        $this->passwordHasher
            ->expects(self::exactly(2))
            ->method('isPasswordValid')
            ->willReturnCallback(static function (User $checkedUser, string $plainPassword) use ($user): bool {
                self::assertSame($user, $checkedUser);

                return match ($plainPassword) {
                    'OldPassword123!' => true,
                    'NewPassword123!' => false,
                    default => false,
                };
            });

        $this->passwordHasher
            ->expects(self::once())
            ->method('hashPassword')
            ->with($user, 'NewPassword123!')
            ->willReturn('hashed-new-password');

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $this->passwordService->changePassword($user, $dto);

        self::assertSame('hashed-new-password', $user->getPassword());
    }

    public function testChangePasswordThrowsExceptionWhenOldPasswordIsInvalid(): void
    {
        $user = $this->createUser();

        $dto = new ChangePasswordRequest(
            oldPassword: 'WrongPassword123!',
            newPassword: 'NewPassword123!',
            confirmNewPassword: 'NewPassword123!',
        );

        $this->passwordHasher
            ->expects(self::once())
            ->method('isPasswordValid')
            ->with($user, 'WrongPassword123!')
            ->willReturn(false);

        $this->passwordHasher
            ->expects(self::never())
            ->method('hashPassword');

        $this->userRepository
            ->expects(self::never())
            ->method('save');

        $this->expectException(InvalidPasswordChangeException::class);
        $this->expectExceptionMessage('Current password is invalid.');

        $this->passwordService->changePassword($user, $dto);
    }

    public function testChangePasswordThrowsExceptionWhenConfirmationDoesNotMatch(): void
    {
        $user = $this->createUser();

        $dto = new ChangePasswordRequest(
            oldPassword: 'OldPassword123!',
            newPassword: 'NewPassword123!',
            confirmNewPassword: 'DifferentPassword123!',
        );

        $this->passwordHasher
            ->expects(self::once())
            ->method('isPasswordValid')
            ->with($user, 'OldPassword123!')
            ->willReturn(true);

        $this->passwordHasher
            ->expects(self::never())
            ->method('hashPassword');

        $this->userRepository
            ->expects(self::never())
            ->method('save');

        $this->expectException(InvalidPasswordChangeException::class);
        $this->expectExceptionMessage('New password and confirmation do not match.');

        $this->passwordService->changePassword($user, $dto);
    }

    public function testChangePasswordThrowsExceptionWhenNewPasswordIsSameAsCurrentPassword(): void
    {
        $user = $this->createUser();

        $dto = new ChangePasswordRequest(
            oldPassword: 'OldPassword123!',
            newPassword: 'OldPassword123!',
            confirmNewPassword: 'OldPassword123!',
        );

        $this->passwordHasher
            ->expects(self::exactly(2))
            ->method('isPasswordValid')
            ->willReturnCallback(static function (User $checkedUser, string $plainPassword) use ($user): bool {
                self::assertSame($user, $checkedUser);

                return 'OldPassword123!' === $plainPassword;
            });

        $this->passwordHasher
            ->expects(self::never())
            ->method('hashPassword');

        $this->userRepository
            ->expects(self::never())
            ->method('save');

        $this->expectException(InvalidPasswordChangeException::class);
        $this->expectExceptionMessage('New password must be different from current password.');

        $this->passwordService->changePassword($user, $dto);
    }

    private function createUser(): User
    {
        return new User(
            email: 'user@example.com',
            username: 'user',
            password: 'hashed-old-password',
        );
    }
}
