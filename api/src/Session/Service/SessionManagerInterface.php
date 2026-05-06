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

namespace App\Session\Service;

use App\Entity\Session;
use App\Entity\User;
use App\Enum\SessionStatus;

interface SessionManagerInterface
{
    public function createSession(
        User $user,
        SessionStatus $status,
        ?string $ipAddress,
        ?string $userAgent,
    ): Session;

    public function assignTokenToSession(Session $session, string $token): void;

    public function findSessionByToken(string $token): ?Session;

    public function markSessionAsAuthenticated(Session $session, string $token): void;

    public function revokeSession(Session $session): void;

    public function deleteSession(string $tokenHash): void;

    public function cleanupExpiredSessions(): void;

    public function assignRefreshTokenToSession(Session $session, string $refreshToken): void;

    public function findSessionByRefreshToken(string $refreshToken): ?Session;

    public function rotateTokens(Session $session, string $accessToken, string $refreshToken): void;
}