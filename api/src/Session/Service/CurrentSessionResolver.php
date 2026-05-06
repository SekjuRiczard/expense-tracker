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

use App\Auth\Service\BearerTokenExtractor;
use App\Entity\Session;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class CurrentSessionResolver
{
    public function __construct(
        private BearerTokenExtractor $bearerTokenExtractor,
        private SessionManagerInterface $sessionManager,
    ) {
    }

    public function resolve(Request $request, User $user): Session
    {
        $token = $this->bearerTokenExtractor->extract($request);
        $session = $this->sessionManager->findSessionByToken($token);

        if (!$session instanceof Session) {
            throw new AccessDeniedHttpException('Invalid or expired session.');
        }

        if ((string) $session->getUser()->getId() !== (string) $user->getId()) {
            throw new AccessDeniedHttpException('Session does not belong to current user.');
        }

        return $session;
    }

    public function resolveToken(Request $request): string
    {
        return $this->bearerTokenExtractor->extract($request);
    }
}