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

namespace App\Session\Service;

use App\Entity\Session;
use App\Entity\User;
use App\Enum\SessionStatus;
use App\Session\Repository\SessionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Throwable;

class SessionService implements SessionManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SessionRepository $sessionRepository,
    ) {
    }

    public function createSession(
        User $user,
        SessionStatus $status,
        ?string $ipAddress,
        ?string $userAgent,
    ): Session {
        /** @var Session $session */
        $session = new Session(
            user: $user,
            tokenHash: $this->generateTemporaryTokenHash(),
            expiresAt: (new DateTimeImmutable())->modify('+1 hour'),
            status: $status,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $session;
    }

    public function assignTokenToSession(Session $session, string $token): void
    {
        $session->setTokenHash($this->hashToken($token));
        $this->entityManager->flush();
    }

    public function findSessionByToken(string $token): ?Session
    {
        /** @var ?Session $session */
        $session = $this->sessionRepository->findOneBy([
            'tokenHash' => $this->hashToken($token),
        ]);
        if (!$session instanceof Session || $session->isRevoked()) {
            return null;
        }
        if ($session->isExpired()) {
            $session->markAsExpired();
            $this->entityManager->flush();

            return null;
        }

        return $session;
    }

    public function markSessionAsAuthenticated(Session $session, string $token): void
    {
        $session->markAsAuthenticated();
        $session->setTokenHash($this->hashToken($token));
        $this->entityManager->flush();
    }

    public function revokeSession(Session $session): void
    {
        $session->revoke();
        $this->entityManager->flush();
    }

    public function deleteSession(string $tokenHash): void
    {
        /** @var ?Session $session */
        $session = $this->sessionRepository->findOneBy(['tokenHash' => $tokenHash]);
        if (!$session instanceof Session) {
            return;
        }
        $this->entityManager->remove($session);
        $this->entityManager->flush();
    }

    public function cleanupExpiredSessions(): void
    {
        $this->sessionRepository->deleteExpiredSessions(new DateTimeImmutable());
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function generateTemporaryTokenHash(): string
    {
        try {
            return hash('sha256', bin2hex(random_bytes(32)));
        } catch (Throwable $exception) {
            throw new RuntimeException('Could not generate temporary session token hash.', 0, $exception);
        }
    }

    public function assignRefreshTokenToSession(Session $session, string $refreshToken): void
    {
        $session->setRefreshTokenHash($this->hashToken($refreshToken));
        $session->setRefreshTokenExpiresAt((new DateTimeImmutable())->modify('+30 days'));
        $this->entityManager->flush();
    }

    public function findSessionByRefreshToken(string $refreshToken): ?Session
    {
        $session = $this->sessionRepository->findOneBy([
            'refreshTokenHash' => $this->hashToken($refreshToken),
        ]);
        if (!$session instanceof Session || $session->isRevoked()) {
            return null;
        }
        if ($session->hasExpiredRefreshToken()) {
            $session->revoke();
            $this->entityManager->flush();
            return null;
        }

        return $session->isAuthenticated() ? $session : null;
    }

    public function rotateTokens(Session $session, string $accessToken, string $refreshToken): void
    {
        $session->setTokenHash($this->hashToken($accessToken));
        $session->setRefreshTokenHash($this->hashToken($refreshToken));
        $session->setRefreshTokenExpiresAt((new DateTimeImmutable())->modify('+30 days'));
        $this->entityManager->flush();
    }
}
