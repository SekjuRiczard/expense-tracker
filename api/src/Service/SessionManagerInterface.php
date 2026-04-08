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

interface SessionManagerInterface {

    public function createSession(User $user, string $tokenHash, ?string $ipAddress, ?string $userAgent): Session;
    public function deleteSession(string $tokenHash): void;
    public function cleanupExpiredSessions(): void;
}