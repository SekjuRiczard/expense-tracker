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

namespace App\Session\Dto\Response;

use App\Entity\Session;
use DateTimeInterface;

class SessionResponse {
    public function __construct(
        public readonly string $id,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly string $createdAt,
        public readonly string $expiresAt
    ) {}

    public static function fromEntity(Session $session): self
    {
        return new self(
            (string) $session->getId(),
            $session->getIpAddress(),
            $session->getUserAgent(),
            $session->getCreatedAt()->format(DateTimeInterface::ATOM),
            $session->getExpiresAt()->format(DateTimeInterface::ATOM)
        );
    }
}