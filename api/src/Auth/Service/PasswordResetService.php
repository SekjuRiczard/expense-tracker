<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Auth\Dto\Request\ForgotPasswordRequest;
use App\Auth\Dto\Request\ResetPasswordRequest;
use App\Auth\Mailer\PasswordResetCodeMailerInterface;
use App\Auth\Repository\PasswordResetCodeRepository;
use App\Auth\Repository\UserRepositoryInterface;
use App\Entity\PasswordResetCode;
use App\Shared\Exception\PasswordResetException;
use DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class PasswordResetService
{
    private const CODE_TTL = '+15 minutes';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetCodeRepository $passwordResetCodeRepository,
        private PasswordResetCodeMailerInterface $passwordResetCodeMailer,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function requestPasswordReset(ForgotPasswordRequest $dto): void
    {
        $user = $this->userRepository->findOneByEmail((string) $dto->email);

        if ($user === null || !$user->isActive()) {
            return;
        }

        $code = $this->generateCode();

        $passwordResetCode = new PasswordResetCode(
            user: $user,
            codeHash: $this->hashCode($code),
            expiresAt: (new DateTimeImmutable())->modify(self::CODE_TTL),
        );

        $this->passwordResetCodeRepository->save($passwordResetCode);
        $this->passwordResetCodeMailer->sendPasswordResetCode($user, $code);
    }

    public function resetPassword(ResetPasswordRequest $dto): void
    {
        $user = $this->userRepository->findOneByEmail((string) $dto->email);

        if ($user === null || !$user->isActive()) {
            throw PasswordResetException::invalidOrExpiredCode();
        }

        $passwordResetCode = $this->passwordResetCodeRepository->findActiveCode(
            user: $user,
            codeHash: $this->hashCode((string) $dto->code),
        );

        if ($passwordResetCode === null || $passwordResetCode->isExpired()) {
            throw PasswordResetException::invalidOrExpiredCode();
        }

        if ($dto->newPassword !== $dto->confirmNewPassword) {
            throw PasswordResetException::passwordsDoNotMatch();
        }

        if ($this->passwordHasher->isPasswordValid($user, (string) $dto->newPassword)) {
            throw PasswordResetException::sameAsCurrentPassword();
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, (string) $dto->newPassword)
        );

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