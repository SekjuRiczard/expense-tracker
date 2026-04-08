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

namespace App\Service;

use App\Entity\Session;
use App\Entity\User;
use App\Repository\Session\SessionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class SessionService implements SessionManagerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly SessionRepository $sessionRepository) {}
    public function createSession(User $user, string $tokenHash, ?string $ipAddress, ?string $userAgent): Session
    {
        /** @var Session $session */
        $session = new Session($user, $tokenHash, (new DateTimeImmutable())->modify('+1 hour'), $ipAddress, $userAgent);
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $session;
    }
    public function deleteSession(string $tokenHash): void
    {
        /** @var ?Session $session */
        $session = $this->sessionRepository->findOneBy(['tokenHash' => $tokenHash]);
        if ($session) {
            $this->entityManager->remove($session);
            $this->entityManager->flush();
        }
    }
    public function cleanupExpiredSessions(): void
    {
        $this->sessionRepository->deleteExpiredSessions(new DateTimeImmutable());
    }
}