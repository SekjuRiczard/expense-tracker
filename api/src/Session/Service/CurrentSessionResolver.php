<?php

declare(strict_types=1);

namespace App\Session\Service;

use App\Entity\Session;
use App\Entity\User;
use App\Auth\Security\CookieTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class CurrentSessionResolver
{
    public function __construct(
        private CookieTokenExtractor $tokenExtractor,
        private SessionManagerInterface $sessionManager,
        private string $logPath,
    ) {
    }

    public function resolve(Request $request, User $user): Session
    {
        $token = $this->tokenExtractor->extract($request);

        file_put_contents(
            $this->logPath,
            sprintf(
                "[%s] token_present=%s token_hash=%s\n",
                (new \DateTimeImmutable())->format('c'),
                is_string($token) ? 'yes' : 'no',
                is_string($token) ? hash('sha256', $token) : 'none',
            ),
            FILE_APPEND
        );

        if (!is_string($token)) {
            throw new AccessDeniedHttpException('JWT token not found.');
        }

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