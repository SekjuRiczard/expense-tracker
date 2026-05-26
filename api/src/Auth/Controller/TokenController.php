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

namespace App\Auth\Controller;

use App\Auth\Factory\CookieFactory;
use App\Auth\Service\AuthTokenService;
use App\Entity\Session;
use App\Session\Service\SessionManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final readonly class TokenController
{
    public function __construct(
        private SessionManagerInterface $sessionManager,
        private AuthTokenService $authTokenService,
    ) {
    }

    #[Route('/api/token/refresh', name: 'auth_token_refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        if (!$request->cookies->get(CookieFactory::REFRESH_TOKEN_COOKIE)) {
            throw new UnauthorizedHttpException('Cookie', 'Refresh token is missing.');
        }
        /** @var Session|null $session */
        $session = $this->sessionManager->findSessionByRefreshToken($request->cookies->get(CookieFactory::REFRESH_TOKEN_COOKIE));
        if (!$session) {
            throw new UnauthorizedHttpException('Cookie', 'Invalid or expired refresh token.');
        }

        return new JsonResponse($this->authTokenService->refreshAuthenticatedToken($session));
    }
}
