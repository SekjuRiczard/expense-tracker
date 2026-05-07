<?php

declare(strict_types=1);

namespace App\Session\Service;

use App\Auth\Security\CookieTokenExtractor;
use App\Entity\Session;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class CurrentSessionResolver
{
    public function __construct(
        private CookieTokenExtractor $tokenExtractor,
        private SessionManagerInterface $sessionManager,
    ) {
    }

    public function resolve(Request $request, User $user): Session
    {
        /** @var string|null $token */
        $token = $this->tokenExtractor->extract($request);

        /** @var Session|null $session */
        $session = $this->sessionManager->findSessionByToken($token);

        if (!$session instanceof Session) {
            throw new AccessDeniedHttpException('Invalid or expired session.');
        }

        if ((string) $session->getUser()->getId() !== (string) $user->getId()) {
            throw new AccessDeniedHttpException('Session does not belong to current user.');
        }

        return $session;
    }
}