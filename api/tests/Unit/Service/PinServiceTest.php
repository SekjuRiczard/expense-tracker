<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Service\PinService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Small]
final class PinServiceTest extends TestCase
{
    #[Test]
    public function setup_pin_hashes_and_saves_pin(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cache = new ArrayAdapter();

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $user = $this->createUser();

        $service = new PinService(
            entityManager: $entityManager,
            cache: $cache,
        );

        $service->setupPin($user, '123456');

        self::assertNotNull($user->getPin());
        self::assertNotSame('123456', $user->getPin());
        self::assertTrue(password_verify('123456', (string) $user->getPin()));
    }

    #[Test]
    public function setup_pin_throws_exception_when_pin_is_already_set(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cache = new ArrayAdapter();

        $entityManager
            ->expects(self::never())
            ->method('flush');

        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $service = new PinService(
            entityManager: $entityManager,
            cache: $cache,
        );

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('PIN is already set up.');

        $service->setupPin($user, '654321');
    }

    #[Test]
    public function verify_pin_returns_true_for_valid_pin(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cache = new ArrayAdapter();

        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $service = new PinService(
            entityManager: $entityManager,
            cache: $cache,
        );

        self::assertTrue($service->verifyPin($user, '123456'));
    }

    #[Test]
    public function verify_pin_returns_false_for_invalid_pin(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cache = new ArrayAdapter();

        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $service = new PinService(
            entityManager: $entityManager,
            cache: $cache,
        );

        self::assertFalse($service->verifyPin($user, '000000'));
    }

    #[Test]
    public function three_invalid_pin_attempts_lock_user(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cache = new ArrayAdapter();

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $service = new PinService(
            entityManager: $entityManager,
            cache: $cache,
        );

        self::assertFalse($service->verifyPin($user, '000000'));
        self::assertFalse($service->verifyPin($user, '000000'));
        self::assertFalse($service->verifyPin($user, '000000'));

        self::assertNotNull($user->getPinLockedUntil());
    }

    #[Test]
    public function verify_pin_throws_exception_when_user_is_locked(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cache = new ArrayAdapter();

        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));
        $user->setPinLockedUntil((new DateTimeImmutable())->modify('+15 minutes'));

        $service = new PinService(
            entityManager: $entityManager,
            cache: $cache,
        );

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('PIN verification locked until');

        $service->verifyPin($user, '123456');
    }

    #[Test]
    public function change_pin_updates_pin_when_old_pin_is_valid(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cache = new ArrayAdapter();

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $service = new PinService(
            entityManager: $entityManager,
            cache: $cache,
        );

        $service->changePin($user, '123456', '654321');

        self::assertTrue(password_verify('654321', (string) $user->getPin()));
        self::assertFalse(password_verify('123456', (string) $user->getPin()));
    }

    #[Test]
    public function change_pin_throws_exception_when_old_pin_is_invalid(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $cache = new ArrayAdapter();

        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $service = new PinService(
            entityManager: $entityManager,
            cache: $cache,
        );

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Invalid old PIN.');

        $service->changePin($user, '000000', '654321');
    }

    private function createUser(): User
    {
        return new User(
            email: sprintf('john_%s@example.com', bin2hex(random_bytes(4))),
            username: 'john',
            password: 'hashed-password',
        );
    }
}