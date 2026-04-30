<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ChangePinRequest;
use App\Dto\SetupPinRequest;
use App\Entity\Session;
use App\Entity\User;
use App\Enum\SessionStatus;
use App\Service\AuthTokenService;
use App\Service\BearerTokenExtractor;
use App\Service\PinService;
use App\Service\SessionManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/pin', name: 'api_pin_')]
final class PinController extends AbstractController
{
    public function __construct(
        private readonly PinService $pinService,
        private readonly SessionManagerInterface $sessionManager,
        private readonly AuthTokenService $authTokenService,
        private readonly BearerTokenExtractor $bearerTokenExtractor,
    ) {
    }

    #[Route('/setup', name: 'setup', methods: ['POST'])]
    public function setupPin(
        #[MapRequestPayload] SetupPinRequest $dto,
        #[CurrentUser] ?User $user,
        Request $request,
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $session = $this->getCurrentSession($request);
        $this->ensureSessionBelongsToUser($session, $user);
        $this->ensureSessionStatus($session, SessionStatus::PIN_SETUP_REQUIRED);

        $this->pinService->setupPin($user, $dto->pin);

        $tokenResponse = $this->authTokenService->createAuthenticatedToken(
            user: $user,
            session: $session,
        );

        return $this->json([
            ...$tokenResponse->toArray(),
            'message' => 'PIN successfully set up.',
            'user' => [
                'id' => (string) $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'hasPin' => $user->getPin() !== null,
            ],
        ], Response::HTTP_OK);
    }

    #[Route('/verify', name: 'verify', methods: ['POST'])]
    public function verifyPin(
        #[MapRequestPayload] SetupPinRequest $dto,
        #[CurrentUser] ?User $user,
        Request $request,
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $session = $this->getCurrentSession($request);
        $this->ensureSessionBelongsToUser($session, $user);
        $this->ensureSessionStatus($session, SessionStatus::PIN_VERIFICATION_REQUIRED);

        if (!$this->pinService->verifyPin($user, $dto->pin)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid PIN.',
            ], Response::HTTP_FORBIDDEN);
        }

        $tokenResponse = $this->authTokenService->createAuthenticatedToken(
            user: $user,
            session: $session,
        );

        return $this->json([
            ...$tokenResponse->toArray(),
            'message' => 'PIN verified successfully.',
            'user' => [
                'id' => (string) $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'hasPin' => $user->getPin() !== null,
            ],
        ], Response::HTTP_OK);
    }

    #[Route('/change', name: 'change', methods: ['PUT'])]
    public function changePin(
        #[MapRequestPayload] ChangePinRequest $dto,
        #[CurrentUser] ?User $user,
        Request $request,
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $session = $this->getCurrentSession($request);
        $this->ensureSessionBelongsToUser($session, $user);
        $this->ensureSessionStatus($session, SessionStatus::AUTHENTICATED);

        $this->pinService->changePin($user, $dto->oldPin, $dto->newPin);

        return $this->json([
            'status' => 'success',
            'message' => 'PIN successfully changed.',
        ], Response::HTTP_OK);
    }

    private function getCurrentSession(Request $request): Session
    {
        $token = $this->bearerTokenExtractor->extract($request);
        $session = $this->sessionManager->findSessionByToken($token);

        if (!$session instanceof Session) {
            throw new AccessDeniedHttpException('Invalid or expired session.');
        }

        return $session;
    }

    private function ensureSessionBelongsToUser(Session $session, User $user): void
    {
        if ((string) $session->getUser()->getId() === (string) $user->getId()) {
            return;
        }

        throw new AccessDeniedHttpException('Session does not belong to current user.');
    }

    private function ensureSessionStatus(Session $session, SessionStatus $expectedStatus): void
    {
        if ($session->getStatus() === $expectedStatus) {
            return;
        }

        throw new AccessDeniedHttpException(sprintf(
            'Invalid session status. Expected "%s", got "%s".',
            $expectedStatus->value,
            $session->getStatus()->value,
        ));
    }
}