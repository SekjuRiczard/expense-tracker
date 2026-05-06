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

namespace App\Auth\Controller;

use App\Auth\Dto\Request\LoginRequest;
use App\Auth\Dto\Request\UserRegistrationRequest;
use App\Auth\Service\AuthService;
use App\Auth\Service\AuthTokenService;
use App\Auth\Service\LoginRateLimiter;
use App\Enum\SessionStatus;
use App\Shared\Exception\InvalidLoginCredentialsException;
use App\Shared\Exception\TooManyLoginAttemptsException;
use App\Shared\Exception\UserAlreadyExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class AuthController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] UserRegistrationRequest $dto,
        Request $request,
        AuthService $authService,
        AuthTokenService $authTokenService,
    ): JsonResponse {
        try {
            $user = $authService->register($dto);
        } catch (UserAlreadyExistsException $exception) {
            return $this->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        }

        $tokenResponse = $authTokenService->createPartialToken(
            user: $user,
            status: SessionStatus::PIN_SETUP_REQUIRED,
            request: $request,
        );

        return $this->json([
            ...$tokenResponse->toArray(),
            'message' => 'User created. PIN setup required.',
            'user' => [
                'id' => (string) $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'hasPin' => $user->getPin() !== null,
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        #[MapRequestPayload] LoginRequest $dto,
        Request $request,
        AuthService $authService,
        AuthTokenService $authTokenService,
        LoginRateLimiter $loginRateLimiter,
    ): JsonResponse {
        try {
            $loginRateLimiter->consume($request, $dto->email);
        } catch (TooManyLoginAttemptsException $exception) {
            return $this->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        try {
            $user = $authService->login($dto);
        } catch (InvalidLoginCredentialsException $exception) {
            return $this->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }

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

        return $this->json([
            ...$tokenResponse->toArray(),
            'message' => $message,
            'user' => [
                'id' => (string) $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'hasPin' => $user->getPin() !== null,
            ],
        ], Response::HTTP_OK);
    }
}
