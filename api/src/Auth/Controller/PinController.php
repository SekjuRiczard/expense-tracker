<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ChangePinRequest;
use App\Dto\SetupPinRequest;
use App\Entity\User;
use App\Enum\SessionStatus;
use App\Factory\ApiResponseFactory;
use App\Service\AuthenticatedUserResolver;
use App\Service\AuthTokenService;
use App\Service\CurrentSessionResolver;
use App\Service\PinService;
use App\Service\SessionStatusGuard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/pin', name: 'api_pin_')]
final class PinController extends AbstractController
{
    public function __construct(
        private readonly PinService $pinService,
        private readonly AuthTokenService $authTokenService,
        private readonly CurrentSessionResolver $currentSessionResolver,
        private readonly ApiResponseFactory $responseFactory,
        private readonly AuthenticatedUserResolver $authenticatedUserResolver,
        private readonly SessionStatusGuard $sessionStatusGuard,
    ) {
    }

    #[Route('/setup', name: 'setup', methods: ['POST'])]
    public function setupPin(
        #[MapRequestPayload] SetupPinRequest $dto,
        #[CurrentUser] ?User $user,
        Request $request,
    ): JsonResponse {
        $user = $this->authenticatedUserResolver->resolve($user);
        $session = $this->currentSessionResolver->resolve($request, $user);
        $this->sessionStatusGuard->ensureStatus($session, SessionStatus::PIN_SETUP_REQUIRED);

        $this->pinService->setupPin($user, $dto->pin);

        $tokenResponse = $this->authTokenService->createAuthenticatedToken(
            user: $user,
            session: $session,
        );

        return $this->responseFactory->tokenResponse(
            tokenResponse: $tokenResponse,
            message: 'PIN successfully set up.',
            user: $user,
            statusCode: Response::HTTP_OK,
        );
    }

    #[Route('/verify', name: 'verify', methods: ['POST'])]
    public function verifyPin(
        #[MapRequestPayload] SetupPinRequest $dto,
        #[CurrentUser] ?User $user,
        Request $request,
    ): JsonResponse {
        $user = $this->authenticatedUserResolver->resolve($user);
        $session = $this->currentSessionResolver->resolve($request, $user);
        $this->sessionStatusGuard->ensureStatus($session, SessionStatus::PIN_VERIFICATION_REQUIRED);

        if (!$this->pinService->verifyPin($user, $dto->pin)) {
            return $this->responseFactory->errorResponse(
                message: 'Invalid PIN.',
                statusCode: Response::HTTP_FORBIDDEN,
            );
        }

        $tokenResponse = $this->authTokenService->createAuthenticatedToken(
            user: $user,
            session: $session,
        );

        return $this->responseFactory->tokenResponse(
            tokenResponse: $tokenResponse,
            message: 'PIN verified successfully.',
            user: $user,
            statusCode: Response::HTTP_OK,
        );
    }

    #[Route('/change', name: 'change', methods: ['PUT'])]
    public function changePin(
        #[MapRequestPayload] ChangePinRequest $dto,
        #[CurrentUser] ?User $user,
        Request $request,
    ): JsonResponse {
        $user = $this->authenticatedUserResolver->resolve($user);
        $session = $this->currentSessionResolver->resolve($request, $user);
        $this->sessionStatusGuard->ensureStatus($session, SessionStatus::AUTHENTICATED);

        $this->pinService->changePin($user, $dto->oldPin, $dto->newPin);

        return $this->responseFactory->successResponse(
            message: 'PIN successfully changed.',
            statusCode: Response::HTTP_OK,
        );
    }
}
