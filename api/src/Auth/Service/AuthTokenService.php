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

namespace App\Service;

use App\Auth\Dto\Response\AuthTokenResponse;
use App\Entity\Session;
use App\Entity\User;
use App\Enum\AuthStage;
use App\Enum\SessionStatus;
use App\Service\Contract\SessionManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class AuthTokenService
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtTokenManager,
        private readonly SessionManagerInterface $sessionManager,
    ) {
    }

    public function createPartialToken(
        User $user,
        SessionStatus $status,
        Request $request,
    ): AuthTokenResponse {
        $session = $this->sessionManager->createSession(
            user: $user,
            status: $status,
            ipAddress: $request->getClientIp(),
            userAgent: $request->headers->get('User-Agent'),
        );

        $authStage = AuthStage::fromSessionStatus($status);

        $token = $this->createAccessToken(
            user: $user,
            session: $session,
            authStage: $authStage,
        );

        $this->sessionManager->assignTokenToSession($session, $token);

        return new AuthTokenResponse(
            token: $token,
            authStage: $authStage,
            session: $session,
        );
    }

    public function createAuthenticatedToken(
        User $user,
        Session $session,
    ): AuthTokenResponse {
        $accessToken = $this->createAccessToken(
            user: $user,
            session: $session,
            authStage: AuthStage::AUTHENTICATED,
        );

        $refreshToken = $this->generateRefreshToken();

        $this->sessionManager->markSessionAsAuthenticated($session, $accessToken);
        $this->sessionManager->assignRefreshTokenToSession($session, $refreshToken);

        return new AuthTokenResponse(
            token: $accessToken,
            authStage: AuthStage::AUTHENTICATED,
            session: $session,
            refreshToken: $refreshToken,
        );
    }

    public function refreshAuthenticatedToken(Session $session): AuthTokenResponse
    {
        $user = $session->getUser();

        $accessToken = $this->createAccessToken(
            user: $user,
            session: $session,
            authStage: AuthStage::AUTHENTICATED,
        );

        $refreshToken = $this->generateRefreshToken();

        $this->sessionManager->rotateTokens(
            session: $session,
            accessToken: $accessToken,
            refreshToken: $refreshToken,
        );

        return new AuthTokenResponse(
            token: $accessToken,
            authStage: AuthStage::AUTHENTICATED,
            session: $session,
            refreshToken: $refreshToken,
        );
    }

    private function createAccessToken(
        User $user,
        Session $session,
        AuthStage $authStage,
    ): string {
        return $this->jwtTokenManager->createFromPayload($user, [
            'session_id' => $session->getIdAsString(),
            'auth_stage' => $authStage->value,
            'has_pin' => $user->getPin() !== null,
            'jti' => $this->generateTokenId(),
        ]);
    }

    private function generateRefreshToken(): string
    {
        try {
            return bin2hex(random_bytes(64));
        } catch (Throwable $exception) {
            throw new RuntimeException('Could not generate refresh token.', 0, $exception);
        }
    }

    private function generateTokenId(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Throwable $exception) {
            throw new RuntimeException('Could not generate token identifier.', 0, $exception);
        }
    }
}