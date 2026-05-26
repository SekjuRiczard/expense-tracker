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

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException as CacheInvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class PinService
{
    public const MAX_FAILED_ATTEMPTS = 3;
    public const LOCKOUT_MINUTES = 15;
    public const CACHE_KEY_PREFIX = 'pin_attempts_user_';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }
    public function setupPin(User $user, string $pin): void
    {
        if (null !== $user->getPin()) {
            throw new BadRequestHttpException('PIN is already set up.');
        }
        $this->savePin($user, $pin);
    }
    public function verifyPin(User $user, string $pin): bool
    {
        $this->ensureUserIsNotLockedOut($user);
        if (password_verify($pin, (string) $user->getPin())) {
            $this->clearFailedAttempts($user);

            return true;
        }
        $this->handleFailedAttempt($user);

        return false;
    }
    public function changePin(User $user, string $oldPin, string $newPin): void
    {
        if (!$this->verifyPin($user, $oldPin)) {
            throw new AccessDeniedHttpException('Invalid old PIN.');
        }
        $this->savePin($user, $newPin);
    }
    private function savePin(User $user, string $pin): void
    {
        $user->setPin(password_hash($pin, PASSWORD_DEFAULT));
        $this->entityManager->flush();
    }
    private function ensureUserIsNotLockedOut(User $user): void
    {
        if (!$user->isPinLocked()) {
            return;
        }
        throw new AccessDeniedHttpException(sprintf('PIN verification locked until %s.', $user->getPinLockedUntil()?->format('Y-m-d H:i:s')));
    }
    private function handleFailedAttempt(User $user): void
    {
        try {
            /** @var CacheItemInterface $cacheItem */
            $cacheItem = $this->cache->getItem(self::CACHE_KEY_PREFIX.$user->getId());
            /** @var int $attempts */
            $attempts = ($cacheItem->isHit() ? (int) $cacheItem->get() : 0) + 1;
            if ($attempts >= self::MAX_FAILED_ATTEMPTS) {
                $this->lockOutUser($user);
                $this->cache->deleteItem(self::CACHE_KEY_PREFIX.$user->getId());

                return;
            }
            $cacheItem->set($attempts);
            $cacheItem->expiresAfter(self::LOCKOUT_MINUTES * 60);
            $this->cache->save($cacheItem);
        } catch (CacheInvalidArgumentException $exception) {
            throw new RuntimeException('Communication error with the cache during PIN attempt handling.', 0, $exception);
        }
    }
    private function lockOutUser(User $user): void
    {
        $user->setPinLockedUntil((new \DateTimeImmutable())->modify(sprintf(
            '+%d minutes',
            self::LOCKOUT_MINUTES,
        )));
        $this->entityManager->flush();
    }
    private function clearFailedAttempts(User $user): void
    {
        try {
            $this->cache->deleteItem(self::CACHE_KEY_PREFIX.$user->getId());
        } catch (CacheInvalidArgumentException $exception) {
            throw new RuntimeException('Communication error with the cache during PIN attempts clearing.', 0, $exception);
        }
        if (null === $user->getPinLockedUntil()) {
            return;
        }
        $user->setPinLockedUntil(null);
        $this->entityManager->flush();
    }
}
