<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Service;

use App\Auth\Service\PinService;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class PinServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private ArrayAdapter $cache;

    private PinService $pinService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->cache = new ArrayAdapter();

        $this->pinService = new PinService(
            entityManager: $this->entityManager,
            cache: $this->cache,
        );
    }

    public function testSetupPinHashesAndStoresPin(): void
    {
        $user = $this->createUser();

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->pinService->setupPin($user, '123456');

        self::assertNotNull($user->getPin());
        self::assertNotSame('123456', $user->getPin());
        self::assertTrue(password_verify('123456', (string) $user->getPin()));
    }

    public function testSetupPinThrowsExceptionWhenPinIsAlreadySet(): void
    {
        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('PIN is already set up.');

        $this->pinService->setupPin($user, '654321');
    }

    public function testVerifyPinReturnsTrueForValidPin(): void
    {
        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        self::assertTrue($this->pinService->verifyPin($user, '123456'));
    }

    public function testVerifyPinReturnsFalseForInvalidPin(): void
    {
        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        self::assertFalse($this->pinService->verifyPin($user, '654321'));
        self::assertNull($user->getPinLockedUntil());
    }

    public function testVerifyPinLocksUserAfterThreeInvalidAttempts(): void
    {
        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        self::assertFalse($this->pinService->verifyPin($user, '654321'));
        self::assertFalse($this->pinService->verifyPin($user, '654321'));
        self::assertFalse($this->pinService->verifyPin($user, '654321'));

        self::assertNotNull($user->getPinLockedUntil());
        self::assertGreaterThan(new DateTimeImmutable(), $user->getPinLockedUntil());
    }

    public function testVerifyPinThrowsAccessDeniedWhenUserIsLocked(): void
    {
        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));
        $user->setPinLockedUntil((new DateTimeImmutable())->modify('+15 minutes'));

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('PIN verification locked until');

        $this->pinService->verifyPin($user, '123456');
    }

    public function testValidPinClearsFailedAttempts(): void
    {
        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        self::assertFalse($this->pinService->verifyPin($user, '654321'));
        self::assertFalse($this->pinService->verifyPin($user, '654321'));

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        self::assertTrue($this->pinService->verifyPin($user, '123456'));

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $pinService = new PinService(
            entityManager: $this->entityManager,
            cache: $this->cache,
        );

        self::assertFalse($pinService->verifyPin($user, '654321'));
        self::assertFalse($pinService->verifyPin($user, '654321'));
        self::assertFalse($pinService->verifyPin($user, '654321'));

        self::assertNotNull($user->getPinLockedUntil());
    }

    public function testChangePinUpdatesPinWhenOldPinIsValid(): void
    {
        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->pinService->changePin($user, '123456', '654321');

        self::assertNotNull($user->getPin());
        self::assertTrue(password_verify('654321', (string) $user->getPin()));
        self::assertFalse(password_verify('123456', (string) $user->getPin()));
    }

    public function testChangePinThrowsAccessDeniedWhenOldPinIsInvalid(): void
    {
        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Invalid old PIN.');

        $this->pinService->changePin($user, '000000', '654321');
    }

    private function createUser(): User
    {
        return new User(
            email: 'user@example.com',
            username: 'user',
            password: 'hashed-password',
        );
    }
}