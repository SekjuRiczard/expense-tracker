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

use App\Auth\Dto\Request\ChangePasswordRequest;
use App\Auth\Dto\Request\ForgotPasswordRequest;
use App\Auth\Dto\Request\ResetPasswordRequest;
use App\Auth\Service\PasswordResetRateLimiter;
use App\Auth\Service\PasswordResetService;
use App\Auth\Service\PasswordService;
use App\Entity\User;
use App\Shared\Exception\InvalidPasswordChangeException;
use App\Shared\Exception\PasswordResetException;
use App\Shared\Exception\TooManyPasswordResetAttemptsException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/api/password')]
final readonly class PasswordController
{
    public function __construct(
        private Security $security,
        private PasswordService $passwordService,
        private PasswordResetService $passwordResetService,
        private PasswordResetRateLimiter $passwordResetRateLimiter,
    ) {
    }

    #[Route('/change', name: 'auth_password_change', methods: ['PATCH'])]
    public function changePassword(#[MapRequestPayload] ChangePasswordRequest $dto): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'status' => 'unauthorized',
                'message' => 'Authentication required.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->passwordService->changePassword($user, $dto);
        } catch (InvalidPasswordChangeException $exception) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Password changed successfully.',
        ], Response::HTTP_OK);
    }

    #[Route('/forgot', name: 'auth_password_forgot', methods: ['POST'])]
    public function forgotPassword(
        #[MapRequestPayload] ForgotPasswordRequest $dto,
        Request $request,
    ): JsonResponse {
        try {
            $this->passwordResetRateLimiter->consume($request, $dto->email);
        } catch (TooManyPasswordResetAttemptsException $exception) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->passwordResetService->requestPasswordReset($dto);

        return new JsonResponse([
            'status' => 'success',
            'message' => 'If this email exists, password reset code has been sent.',
        ], Response::HTTP_OK);
    }

    #[Route('/reset', name: 'auth_password_reset', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload] ResetPasswordRequest $dto,
        Request $request,
    ): JsonResponse {
        try {
            $this->passwordResetRateLimiter->consume($request, $dto->email);
            $this->passwordResetService->resetPassword($dto);
        } catch (TooManyPasswordResetAttemptsException $exception) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        } catch (PasswordResetException $exception) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Password has been reset successfully.',
        ], Response::HTTP_OK);
    }
}
