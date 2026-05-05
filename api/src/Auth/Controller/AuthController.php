<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Auth\Dto\Request\LoginRequest;
use App\Auth\Dto\Request\UserRegistrationRequest;
use App\Auth\Factory\ApiResponseFactory;
use App\Auth\Service\AuthService;
use App\Auth\Service\AuthTokenService;
use App\Auth\Service\LoginRateLimiter;
use App\Enum\SessionStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly ApiResponseFactory $responseFactory,
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] UserRegistrationRequest $dto,
        Request $request,
        AuthService $authService,
        AuthTokenService $authTokenService,
    ): JsonResponse {
        $user = $authService->register($dto);

        $tokenResponse = $authTokenService->createPartialToken(
            user: $user,
            status: SessionStatus::PIN_SETUP_REQUIRED,
            request: $request,
        );

        return $this->responseFactory->tokenResponse(
            tokenResponse: $tokenResponse,
            message: 'User created. PIN setup required.',
            user: $user,
            statusCode: Response::HTTP_CREATED,
        );
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        #[MapRequestPayload] LoginRequest $dto,
        Request $request,
        AuthService $authService,
        AuthTokenService $authTokenService,
        LoginRateLimiter $loginRateLimiter,
    ): JsonResponse {
        $loginRateLimiter->consume($request, $dto->email);
        $user = $authService->login($dto);

        $sessionStatus = $user->getPin() === null
            ? SessionStatus::PIN_SETUP_REQUIRED
            : SessionStatus::PIN_VERIFICATION_REQUIRED;

        $tokenResponse = $authTokenService->createPartialToken(
            user: $user,
            status: $sessionStatus,
            request: $request,
        );

        $message = $sessionStatus === SessionStatus::PIN_SETUP_REQUIRED
            ? 'Password verified. PIN setup required.'
            : 'Password verified. PIN verification required.';

        return $this->responseFactory->tokenResponse(
            tokenResponse: $tokenResponse,
            message: $message,
            user: $user,
            statusCode: Response::HTTP_OK,
        );
    }
}
