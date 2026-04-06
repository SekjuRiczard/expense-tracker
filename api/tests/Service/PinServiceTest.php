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

use App\Entity\User;
use App\Service\PinService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use DateTime;

#[CoversClass(PinService::class)]
#[Small]
final class PinServiceTest extends TestCase
{
    private function createSut(): array
    {
        /** @var EntityManagerInterface&MockObject $entityManager */
        /** @var CacheItemPoolInterface&MockObject $cache */
        return [new PinService($entityManager = $this->createMock(EntityManagerInterface::class), $cache = $this->createMock(CacheItemPoolInterface::class)), $entityManager, $cache];
    }
    #[Test]
    public function it_throws_exception_if_pin_is_already_set(): void
    {
        /** @var PinService $service */
        [$service] = $this->createSut();
        /** @var User $user */
        ($user = new User('test_user', 'test@example.com', 'dummy_password'))->setPin('existing_hash');
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('PIN is already set up.');
        $service->setupPin($user, '123456');
    }
    #[Test]
    public function it_saves_hashed_pin_on_setup(): void
    {
        /** @var PinService $service */
        /** @var EntityManagerInterface&MockObject $entityManager */
        [$service, $entityManager] = $this->createSut();
        /** @var User $user */
        $user = new User('test_user', 'test@example.com', 'dummy_password');
        $entityManager->expects($this->once())->method('flush');
        $service->setupPin($user, '123456');
        $this->assertNotNull($user->getPin());
        $this->assertTrue(password_verify('123456', (string) $user->getPin()));
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function it_clears_cache_attempts_and_returns_true_on_valid_pin(): void
    {
        /** @var PinService $service */
        /** @var CacheItemPoolInterface&MockObject $cache */
        [$service, , $cache] = $this->createSut();
        /** @var User $user */
        $user = new User('test_user', 'test@example.com', 'dummy_password');
        /** @var string $uuidString */
        $uuidString = '123e4567-e89b-12d3-a456-426614174000';
        (new ReflectionClass($user))->getProperty('id')->setValue($user, Uuid::fromString($uuidString));
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));
        $cache->expects($this->once())->method('deleteItem')->with('pin_attempts_user_' . $uuidString);
        $this->assertTrue($service->verifyPin($user, '123456'));
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function it_locks_user_out_after_3_failed_attempts(): void
    {
        /** @var PinService $service */
        /** @var EntityManagerInterface&MockObject $entityManager */
        /** @var CacheItemPoolInterface&MockObject $cache */
        [$service, $entityManager, $cache] = $this->createSut();
        /** @var User $user */
        $user = new User('test_user', 'test@example.com', 'dummy_password');
        /** @var string $uuidString */
        $uuidString = '123e4567-e89b-12d3-a456-426614174000';
        (new ReflectionClass($user))->getProperty('id')->setValue($user, Uuid::fromString($uuidString));
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));
        /** @var CacheItemInterface&MockObject $cacheItem */
        $cache->expects($this->once())->method('getItem')->with('pin_attempts_user_' . $uuidString)->willReturn($cacheItem = $this->createMock(CacheItemInterface::class));
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn(2);
        $cache->expects($this->once())->method('deleteItem')->with('pin_attempts_user_' . $uuidString);
        $entityManager->expects($this->once())->method('flush');
        $this->assertFalse($service->verifyPin($user, 'wrong_pin'));
        $this->assertNotNull($user->getPinLockedUntil());
    }
    #[Test]
    public function it_throws_exception_when_verifying_locked_pin(): void
    {
        /** @var PinService $service */
        [$service] = $this->createSut();
        /** @var User $user */
        ($user = new User('test_user', 'test@example.com', 'dummy_password'))->setPinLockedUntil((new DateTime())->modify('+10 minutes'));
        $this->expectException(AccessDeniedHttpException::class);
        $service->verifyPin($user, '123456');
    }
    #[Test]
    public function it_changes_pin_successfully(): void
    {
        /** @var PinService $service */
        /** @var EntityManagerInterface&MockObject $entityManager */
        [$service, $entityManager] = $this->createSut();
        /** @var User $user */
        ($user = new User('test_user', 'test@example.com', 'dummy_password'))->setPin(password_hash('123456', PASSWORD_DEFAULT));
        $entityManager->expects($this->once())->method('flush');
        $service->changePin($user, '123456', '654321');
        $this->assertTrue(password_verify('654321', (string) $user->getPin()));
    }
}