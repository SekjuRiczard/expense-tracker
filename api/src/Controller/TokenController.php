<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Session;
use App\Factory\ApiResponseFactory;
use App\Service\AuthTokenService;
use App\Service\SessionManagerInterface;
use App\Service\Token\Refresh\BodyRefreshTokenResolver;
use App\Service\Token\Refresh\RefreshTokenResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/token', name: 'api_token_')]
final class TokenController extends AbstractController
{
    public function __construct(
        private readonly SessionManagerInterface $sessionManager,
        private readonly AuthTokenService $authTokenService,
        private readonly ApiResponseFactory $responseFactory,
        #[Autowire(service: BodyRefreshTokenResolver::class)]
        private readonly RefreshTokenResolverInterface $refreshTokenResolver,
    ) {
    }

    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $this->refreshTokenResolver->resolve($request);
        $session = $refreshToken !== null
            ? $this->sessionManager->findSessionByRefreshToken($refreshToken)
            : null;

        if (!$session instanceof Session) {
            return $this->responseFactory->errorResponse(
                message: 'Invalid or expired refresh token.',
                statusCode: Response::HTTP_UNAUTHORIZED,
            );
        }

        $tokenResponse = $this->authTokenService->refreshAuthenticatedToken($session);

        return $this->responseFactory->tokenResponse(
            tokenResponse: $tokenResponse,
            message: 'Token refreshed successfully.',
            user: $session->getUser(),
            statusCode: Response::HTTP_OK,
        );
    }
}
