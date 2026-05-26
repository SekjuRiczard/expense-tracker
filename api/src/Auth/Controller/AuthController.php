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

use App\Auth\Dto\Request\LoginRequest;
use App\Auth\Dto\Request\UserRegistrationRequest;
use App\Auth\Service\AuthService;
use App\Auth\Service\AuthTokenService;
use App\Auth\Service\LoginRateLimiter;
use App\Entity\User;
use App\Enum\ResponseMessage;
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
            /** @var User $user */
            $user = $authService->register($dto);
        } catch (UserAlreadyExistsException $exception) {

            return $this->json(['status' => 'error', 'message' => $exception->getMessage()], Response::HTTP_CONFLICT);
        }

        return $this->json(
            $authTokenService->createPartialToken(
                $user,
                SessionStatus::PIN_SETUP_REQUIRED,
                $request,
                ResponseMessage::REGISTER_SUCCESS
            )->toArray(),
            Response::HTTP_CREATED
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
        try {
            $loginRateLimiter->consume($request, $dto->email);
            /** @var User $user */
            $user = $authService->login($dto);
        } catch (TooManyLoginAttemptsException|InvalidLoginCredentialsException $exception) {

            return $this->json(['status' => 'error', 'message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
        /** @var bool $hasPin */
        $hasPin = null !== $user->getPin();

        return $this->json(
            $authTokenService->createPartialToken(
                $user,
                $hasPin ? SessionStatus::PIN_VERIFICATION_REQUIRED : SessionStatus::PIN_SETUP_REQUIRED,
                $request,
                $hasPin ? ResponseMessage::LOGIN_SUCCESS : ResponseMessage::PIN_SETUP_REQUIRED
            )->toArray()
        );
    }
}
