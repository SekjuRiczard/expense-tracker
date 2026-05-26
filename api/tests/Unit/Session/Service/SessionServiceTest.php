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

namespace App\Tests\Unit\Session\Service;

use App\Entity\Session;
use App\Entity\User;
use App\Enum\SessionStatus;
use App\Session\Repository\SessionRepository;
use App\Session\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class SessionServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private SessionRepository $sessionRepository;

    private SessionService $sessionService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->sessionRepository = $this->createMock(SessionRepository::class);

        $this->sessionService = new SessionService(
            entityManager: $this->entityManager,
            sessionRepository: $this->sessionRepository,
        );
    }

    public function testCreateSessionPersistsAndFlushesNewSession(): void
    {
        $user = $this->createUser();

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(Session::class));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $session = $this->sessionService->createSession(
            user: $user,
            status: SessionStatus::PIN_SETUP_REQUIRED,
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
        );

        self::assertSame($user, $session->getUser());
        self::assertSame(SessionStatus::PIN_SETUP_REQUIRED, $session->getStatus());
        self::assertSame('127.0.0.1', $session->getIpAddress());
        self::assertSame('PHPUnit', $session->getUserAgent());
        self::assertNotSame('', $session->getTokenHash());
        self::assertGreaterThan(new \DateTimeImmutable(), $session->getExpiresAt());
    }

    public function testAssignTokenToSessionStoresTokenHashAndFlushes(): void
    {
        $session = $this->createSession();

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->sessionService->assignTokenToSession($session, 'plain-access-token');

        self::assertSame(hash('sha256', 'plain-access-token'), $session->getTokenHash());
        self::assertNotSame('plain-access-token', $session->getTokenHash());
    }

    public function testFindSessionByTokenReturnsNullWhenSessionDoesNotExist(): void
    {
        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'tokenHash' => hash('sha256', 'missing-token'),
            ])
            ->willReturn(null);

        self::assertNull($this->sessionService->findSessionByToken('missing-token'));
    }

    public function testFindSessionByTokenReturnsActiveSession(): void
    {
        $session = $this->createSession(
            status: SessionStatus::AUTHENTICATED,
            expiresAt: (new \DateTimeImmutable())->modify('+1 hour'),
        );

        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'tokenHash' => hash('sha256', 'valid-access-token'),
            ])
            ->willReturn($session);

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        self::assertSame($session, $this->sessionService->findSessionByToken('valid-access-token'));
    }

    public function testFindSessionByTokenMarksExpiredSessionAsExpiredAndReturnsNull(): void
    {
        $session = $this->createSession(
            status: SessionStatus::AUTHENTICATED,
            expiresAt: (new \DateTimeImmutable())->modify('-1 minute'),
        );

        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn($session);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertNull($this->sessionService->findSessionByToken('expired-access-token'));
        self::assertSame(SessionStatus::EXPIRED, $session->getStatus());
    }

    public function testFindSessionByTokenReturnsNullForRevokedSession(): void
    {
        $session = $this->createSession(
            status: SessionStatus::AUTHENTICATED,
            expiresAt: (new \DateTimeImmutable())->modify('+1 hour'),
        );
        $session->revoke();

        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn($session);

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        self::assertNull($this->sessionService->findSessionByToken('revoked-access-token'));
    }

    public function testMarkSessionAsAuthenticatedChangesStatusStoresTokenHashAndFlushes(): void
    {
        $session = $this->createSession(status: SessionStatus::PIN_SETUP_REQUIRED);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->sessionService->markSessionAsAuthenticated($session, 'authenticated-access-token');

        self::assertSame(SessionStatus::AUTHENTICATED, $session->getStatus());
        self::assertSame(hash('sha256', 'authenticated-access-token'), $session->getTokenHash());
        self::assertNotNull($session->getAuthenticatedAt());
        self::assertNull($session->getRevokedAt());
    }

    public function testRevokeSessionMarksSessionAsRevokedAndFlushes(): void
    {
        $session = $this->createSession(status: SessionStatus::AUTHENTICATED);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->sessionService->revokeSession($session);

        self::assertSame(SessionStatus::REVOKED, $session->getStatus());
        self::assertTrue($session->isRevoked());
        self::assertNotNull($session->getRevokedAt());
    }

    public function testDeleteSessionDoesNothingWhenSessionDoesNotExist(): void
    {
        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['tokenHash' => 'missing-token-hash'])
            ->willReturn(null);

        $this->entityManager
            ->expects(self::never())
            ->method('remove');

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->sessionService->deleteSession('missing-token-hash');
    }

    public function testDeleteSessionRemovesExistingSessionAndFlushes(): void
    {
        $session = $this->createSession();

        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['tokenHash' => 'existing-token-hash'])
            ->willReturn($session);

        $this->entityManager
            ->expects(self::once())
            ->method('remove')
            ->with($session);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->sessionService->deleteSession('existing-token-hash');
    }

    public function testAssignRefreshTokenToSessionStoresRefreshTokenHashExpiryAndFlushes(): void
    {
        $session = $this->createSession(status: SessionStatus::AUTHENTICATED);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->sessionService->assignRefreshTokenToSession($session, 'plain-refresh-token');

        self::assertSame(hash('sha256', 'plain-refresh-token'), $session->getRefreshTokenHash());
        self::assertNotSame('plain-refresh-token', $session->getRefreshTokenHash());
        self::assertNotNull($session->getRefreshTokenExpiresAt());
        self::assertGreaterThan(new \DateTimeImmutable(), $session->getRefreshTokenExpiresAt());
    }

    public function testFindSessionByRefreshTokenReturnsNullWhenSessionDoesNotExist(): void
    {
        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'refreshTokenHash' => hash('sha256', 'missing-refresh-token'),
            ])
            ->willReturn(null);

        self::assertNull($this->sessionService->findSessionByRefreshToken('missing-refresh-token'));
    }

    public function testFindSessionByRefreshTokenReturnsAuthenticatedSession(): void
    {
        $session = $this->createSession(status: SessionStatus::AUTHENTICATED);
        $session->setRefreshTokenHash(hash('sha256', 'valid-refresh-token'));
        $session->setRefreshTokenExpiresAt((new \DateTimeImmutable())->modify('+30 days'));

        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'refreshTokenHash' => hash('sha256', 'valid-refresh-token'),
            ])
            ->willReturn($session);

        self::assertSame($session, $this->sessionService->findSessionByRefreshToken('valid-refresh-token'));
    }

    public function testFindSessionByRefreshTokenReturnsNullForRevokedSession(): void
    {
        $session = $this->createSession(status: SessionStatus::AUTHENTICATED);
        $session->setRefreshTokenExpiresAt((new \DateTimeImmutable())->modify('+30 days'));
        $session->revoke();

        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn($session);

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        self::assertNull($this->sessionService->findSessionByRefreshToken('revoked-refresh-token'));
    }

    public function testFindSessionByRefreshTokenRevokesExpiredRefreshSessionAndReturnsNull(): void
    {
        $session = $this->createSession(status: SessionStatus::AUTHENTICATED);
        $session->setRefreshTokenExpiresAt((new \DateTimeImmutable())->modify('-1 minute'));

        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn($session);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertNull($this->sessionService->findSessionByRefreshToken('expired-refresh-token'));
        self::assertSame(SessionStatus::REVOKED, $session->getStatus());
        self::assertNotNull($session->getRevokedAt());
    }

    public function testFindSessionByRefreshTokenReturnsNullForNotAuthenticatedSession(): void
    {
        $session = $this->createSession(status: SessionStatus::PIN_VERIFICATION_REQUIRED);
        $session->setRefreshTokenExpiresAt((new \DateTimeImmutable())->modify('+30 days'));

        $this->sessionRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->willReturn($session);

        self::assertNull($this->sessionService->findSessionByRefreshToken('partial-refresh-token'));
    }

    public function testRotateTokensUpdatesAccessRefreshHashesExpiryAndFlushes(): void
    {
        $session = $this->createSession(status: SessionStatus::AUTHENTICATED);
        $session->setTokenHash(hash('sha256', 'old-access-token'));
        $session->setRefreshTokenHash(hash('sha256', 'old-refresh-token'));
        $session->setRefreshTokenExpiresAt((new \DateTimeImmutable())->modify('+1 day'));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->sessionService->rotateTokens(
            session: $session,
            accessToken: 'new-access-token',
            refreshToken: 'new-refresh-token',
        );

        self::assertSame(hash('sha256', 'new-access-token'), $session->getTokenHash());
        self::assertSame(hash('sha256', 'new-refresh-token'), $session->getRefreshTokenHash());
        self::assertNotSame(hash('sha256', 'old-access-token'), $session->getTokenHash());
        self::assertNotSame(hash('sha256', 'old-refresh-token'), $session->getRefreshTokenHash());
        self::assertNotNull($session->getRefreshTokenExpiresAt());
        self::assertGreaterThan(new \DateTimeImmutable(), $session->getRefreshTokenExpiresAt());
    }

    public function testCleanupExpiredSessionsDelegatesToRepository(): void
    {
        $this->sessionRepository
            ->expects(self::once())
            ->method('deleteExpiredSessions')
            ->with(self::isInstanceOf(\DateTimeImmutable::class));

        $this->sessionService->cleanupExpiredSessions();
    }

    private function createUser(): User
    {
        return new User(
            email: 'user@example.com',
            username: 'user',
            password: 'hashed-password',
        );
    }

    private function createSession(
        SessionStatus $status = SessionStatus::AUTHENTICATED,
        ?\DateTimeImmutable $expiresAt = null,
    ): Session {
        return new Session(
            user: $this->createUser(),
            tokenHash: 'initial-token-hash',
            expiresAt: $expiresAt ?? (new \DateTimeImmutable())->modify('+1 hour'),
            status: $status,
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
        );
    }
}
