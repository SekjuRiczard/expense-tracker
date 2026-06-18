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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
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
        /** @var string|null $refreshToken */
        $refreshToken = $request->cookies->get(CookieFactory::REFRESH_TOKEN_COOKIE);
        if (!$refreshToken) {
            return new JsonResponse([
                'status' => 'unauthorized',
                'message' => 'Refresh token is missing.',
            ], Response::HTTP_UNAUTHORIZED);
        }
        /** @var Session|null $session */
        $session = $this->sessionManager->findSessionByRefreshToken($refreshToken);
        if (!$session) {
            // The refresh token is present but expired/invalid. Clear all auth
            // cookies so the stale state cannot block a fresh login.
            return $this->clearedUnauthorizedResponse(
                $request,
                'Invalid or expired refresh token.',
            );
        }

        return new JsonResponse($this->authTokenService->refreshAuthenticatedToken($session)->toArray());
    }

    private function clearedUnauthorizedResponse(
        Request $request,
        string $message,
    ): JsonResponse {
        // Flag the request so that AuthCookieSubscriber expires all auth cookies,
        // ensuring a stale/expired refresh token does not block a fresh login.
        $request->attributes->set('_logout', true);

        return new JsonResponse([
            'status' => 'unauthorized',
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }
}
