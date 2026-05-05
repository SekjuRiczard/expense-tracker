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

namespace App\Auth\Service;

use App\Shared\Exception\TooManyLoginAttemptsException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final readonly class LoginRateLimiter
{
    public function __construct(
        private RateLimiterFactory $loginLimiter,
    ) {
    }

    public function consume(Request $request, ?string $email): void
    {
        $limiter = $this->loginLimiter->create($this->createLimiterKey($request, $email));
        $limit = $limiter->consume(1);

        if ($limit->isAccepted()) {
            return;
        }

        throw TooManyLoginAttemptsException::create();
    }

    private function createLimiterKey(Request $request, ?string $email): string
    {
        $normalizedEmail = mb_strtolower(trim((string) $email));
        $ipAddress = $request->getClientIp() ?? 'unknown';

        if ($normalizedEmail === '') {
            return sprintf('anonymous:%s', $ipAddress);
        }

        return sprintf('%s:%s', $normalizedEmail, $ipAddress);
    }
}