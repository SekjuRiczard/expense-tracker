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

use App\Shared\Exception\TooManyLoginAttemptsException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final readonly class LoginRateLimiter
{
    public function __construct(
        private RateLimiterFactory $loginLimiter,
    ) {
    }

    public function consume(Request $request, ?string $email): void
    {
        /** @var LimiterInterface $limiter */
        $limiter = $this->loginLimiter->create($this->createLimiterKey($request, $email));
        if ($limiter->consume(1)->isAccepted()) {

            return;
        }
        throw TooManyLoginAttemptsException::create();
    }

    private function createLimiterKey(Request $request, ?string $email): string
    {
        /** @var string $email */
        $email = mb_strtolower(trim((string) $email));

        return ('' === $email ? 'anonymous' : $email).':'.($request->getClientIp() ?? 'unknown');
    }
}
