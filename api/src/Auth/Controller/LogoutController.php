<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Entity\Session;
use App\Session\Service\SessionManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final readonly class LogoutController
{
    public function __construct(
        private SessionManagerInterface $sessionManager,
    ) {
    }

    #[Route('/api/logout', name: 'auth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        /** @var Session|null $session */
        $session = $request->attributes->get('app_session');

        if ($session instanceof Session) {
            $this->sessionManager->revokeSession($session);
        }

        $request->attributes->set('_logout', true);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}