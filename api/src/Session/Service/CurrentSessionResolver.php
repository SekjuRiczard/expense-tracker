<?php

declare(strict_types=1);

namespace App\Service;

use App\Auth\Service\Token\Extractor\BearerRequestTokenExtractor;
use App\Auth\Service\Token\Extractor\RequestTokenExtractorInterface;
use App\Entity\Session;
use App\Entity\User;
use App\Service\Contract\SessionManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class CurrentSessionResolver
{
    public function __construct(
        #[Autowire(service: BearerRequestTokenExtractor::class)]
        private RequestTokenExtractorInterface $tokenExtractor,
        private SessionManagerInterface $sessionManager,
    ) {
    }

    public function resolve(Request $request, User $user): Session
    {
        $token = $this->tokenExtractor->extract($request);
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
        return $this->tokenExtractor->extract($request);
    }
}
