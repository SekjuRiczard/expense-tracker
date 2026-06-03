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

namespace App\Auth\Service;

use App\Auth\Dto\Response\AuthTokenResponse;
use App\Entity\Session;
use App\Entity\User;
use App\Enum\ResponseMessage;
use App\Enum\SessionStatus;
use App\Session\Service\SessionManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

final readonly class AuthTokenService
{
    public function __construct(
        private JWTTokenManagerInterface $jwtTokenManager,
        private SessionManagerInterface $sessionManager,
        private RequestStack $requestStack,
    ) {
    }

    public function createPartialToken(User $user, SessionStatus $status, Request $request, ResponseMessage $message): AuthTokenResponse
    {
        /** @var Session $session */
        $session = $this->sessionManager->createSession($user, $status, $request->getClientIp(), $request->headers->get('User-Agent'));
        /** @var string $token */
        $token = $this->createAccessToken($user, $session, $status);
        $this->sessionManager->assignTokenToSession($session, $token);
        $this->requestStack->getCurrentRequest()->attributes->set('_partial_auth_token', $token);

        return new AuthTokenResponse($status, $user, $message);
    }

    public function createAuthenticatedToken(User $user, Session $session, ResponseMessage $message): AuthTokenResponse
    {
        /** @var string $token */
        $token = $this->createAccessToken($user, $session, SessionStatus::AUTHENTICATED);
        /** @var string $refresh */
        $refresh = $this->generateRefreshToken();
        $this->sessionManager->markSessionAsAuthenticated($session, $token);
        $this->sessionManager->assignRefreshTokenToSession($session, $refresh);
        $this->requestStack->getCurrentRequest()->attributes->set('_auth_token', $token);
        $this->requestStack->getCurrentRequest()->attributes->set('_refresh_token', $refresh);
        $this->requestStack->getCurrentRequest()->attributes->set('_expire_partial', true);

        return new AuthTokenResponse(SessionStatus::AUTHENTICATED, $user, $message);
    }

    public function refreshAuthenticatedToken(Session $session): AuthTokenResponse
    {
        /** @var string $token */
        $token = $this->createAccessToken($session->getUser(), $session, SessionStatus::AUTHENTICATED);
        /** @var string $refresh */
        $refresh = $this->generateRefreshToken();
        $this->sessionManager->rotateTokens($session, $token, $refresh);
        $this->requestStack->getCurrentRequest()->attributes->set('_auth_token', $token);
        $this->requestStack->getCurrentRequest()->attributes->set('_refresh_token', $refresh);

        return new AuthTokenResponse(SessionStatus::AUTHENTICATED, $session->getUser(), ResponseMessage::SESSION_REFRESHED);
    }

    private function createAccessToken(User $user, Session $session, SessionStatus $status): string
    {
        return $this->jwtTokenManager->createFromPayload($user, [
            'session_id' => $session->getIdAsString(),
            'status' => $status->value,
            'has_pin' => null !== $user->getPin(),
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
