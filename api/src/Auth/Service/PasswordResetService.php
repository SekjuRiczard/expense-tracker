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

use App\Auth\Dto\Request\ForgotPasswordRequest;
use App\Auth\Dto\Request\ResetPasswordRequest;
use App\Auth\Mailer\PasswordResetCodeMailerInterface;
use App\Auth\Repository\PasswordResetCodeRepository;
use App\Auth\Repository\UserRepositoryInterface;
use App\Entity\PasswordResetCode;
use App\Entity\User;
use App\Shared\Exception\PasswordResetException;
use DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class PasswordResetService
{
    public const CODE_TTL = '+15 minutes';
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetCodeRepository $passwordResetCodeRepository,
        private PasswordResetCodeMailerInterface $passwordResetCodeMailer,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }
    public function requestPasswordReset(ForgotPasswordRequest $dto): void
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneByEmail((string) $dto->email);
        if (null !== $user && $user->isActive()) {
            /** @var string $code */
            $code = $this->generateCode();
            $this->passwordResetCodeRepository->save(new PasswordResetCode(
                user: $user,
                codeHash: $this->hashCode($code),
                expiresAt: (new DateTimeImmutable())->modify(self::CODE_TTL),
            ));
            $this->passwordResetCodeMailer->sendPasswordResetCode($user, $code);
        }
    }
    public function resetPassword(ResetPasswordRequest $dto): void
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneByEmail((string) $dto->email);
        if (null === $user || !$user->isActive()) {
            throw PasswordResetException::invalidOrExpiredCode();
        }
        /** @var PasswordResetCode|null $passwordResetCode */
        $passwordResetCode = $this->passwordResetCodeRepository->findActiveCode(
            user: $user,
            codeHash: $this->hashCode((string) $dto->code),
        );
        if (null === $passwordResetCode || $passwordResetCode->isExpired()) {
            throw PasswordResetException::invalidOrExpiredCode();
        }
        match (true) {
            $dto->newPassword !== $dto->confirmNewPassword => throw PasswordResetException::passwordsDoNotMatch(),
            $this->passwordHasher->isPasswordValid($user, (string) $dto->newPassword) => throw PasswordResetException::sameAsCurrentPassword(),
            default => null,
        };
        $user->setPassword($this->passwordHasher->hashPassword($user, (string) $dto->newPassword));
        $passwordResetCode->markAsUsed();
        $this->userRepository->save($user);
    }
    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }
    private function hashCode(string $code): string
    {
        return hash('sha256', $code);
    }
}
