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

namespace App\Controller;

use App\Dto\RefreshTokenRequest;
use App\Entity\Session;
use App\Service\AuthTokenService;
use App\Service\SessionManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/token', name: 'api_token_')]
final class TokenController extends AbstractController
{
    public function __construct(
        private readonly SessionManagerInterface $sessionManager,
        private readonly AuthTokenService $authTokenService,
    ) {
    }

    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(
        #[MapRequestPayload] RefreshTokenRequest $dto,
    ): JsonResponse {
        $session = $this->sessionManager->findSessionByRefreshToken((string) $dto->refreshToken);

        if (!$session instanceof Session) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid or expired refresh token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $tokenResponse = $this->authTokenService->refreshAuthenticatedToken($session);

        return $this->json([
            ...$tokenResponse->toArray(),
            'message' => 'Token refreshed successfully.',
            'user' => [
                'id' => (string) $session->getUser()->getId(),
                'email' => $session->getUser()->getEmail(),
                'username' => $session->getUser()->getUsername(),
                'hasPin' => $session->getUser()->getPin() !== null,
            ],
        ], Response::HTTP_OK);
    }
}