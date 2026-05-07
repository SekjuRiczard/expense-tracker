<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Service;

use App\Auth\Dto\Response\AuthTokenResponse;
use App\Auth\Service\AuthTokenService;
use App\Entity\Session;
use App\Entity\User;
use App\Enum\ResponseMessage;
use App\Enum\SessionStatus;
use App\Session\Service\SessionManagerInterface;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class AuthTokenServiceTest extends TestCase
{
    private JWTTokenManagerInterface $jwtTokenManager;

    private SessionManagerInterface $sessionManager;

    private RequestStack $requestStack;

    private Request $request;

    private AuthTokenService $authTokenService;

    protected function setUp(): void
    {
        $this->jwtTokenManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);

        $this->request = Request::create('/api/test');
        $this->request->server->set('REMOTE_ADDR', '127.0.0.1');
        $this->request->headers->set('User-Agent', 'PHPUnit');

        $this->requestStack = new RequestStack();
        $this->requestStack->push($this->request);

        $this->authTokenService = new AuthTokenService(
            jwtTokenManager: $this->jwtTokenManager,
            sessionManager: $this->sessionManager,
            requestStack: $this->requestStack,
        );
    }

    public function testCreatePartialTokenCreatesSessionAssignsTokenAndSetsRequestAttribute(): void
    {
        $user = $this->createUser();
        $session = $this->createSession($user, SessionStatus::PIN_SETUP_REQUIRED);

        $this->sessionManager
            ->expects(self::once())
            ->method('createSession')
            ->with(
                $user,
                SessionStatus::PIN_SETUP_REQUIRED,
                '127.0.0.1',
                'PHPUnit',
            )
            ->willReturn($session);

        $this->jwtTokenManager
            ->expects(self::once())
            ->method('createFromPayload')
            ->with(
                $user,
                self::callback(static function (array $payload): bool {
                    return $payload['session_id'] === ''
                        && $payload['status'] === SessionStatus::PIN_SETUP_REQUIRED->value
                        && $payload['has_pin'] === false
                        && is_string($payload['jti'])
                        && $payload['jti'] !== '';
                }),
            )
            ->willReturn('partial-jwt-token');

        $this->sessionManager
            ->expects(self::once())
            ->method('assignTokenToSession')
            ->with($session, 'partial-jwt-token');

        $response = $this->authTokenService->createPartialToken(
            user: $user,
            status: SessionStatus::PIN_SETUP_REQUIRED,
            request: $this->request,
            message: ResponseMessage::PIN_SETUP_REQUIRED,
        );

        self::assertInstanceOf(AuthTokenResponse::class, $response);
        self::assertSame('partial-jwt-token', $this->request->attributes->get('_partial_auth_token'));
        self::assertFalse($this->request->attributes->has('_auth_token'));
        self::assertFalse($this->request->attributes->has('_refresh_token'));
        self::assertFalse($this->request->attributes->has('_expire_partial'));
    }

    public function testCreatePartialTokenPayloadContainsHasPinTrueForUserWithPin(): void
    {
        $user = $this->createUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));

        $session = $this->createSession($user, SessionStatus::PIN_VERIFICATION_REQUIRED);

        $this->sessionManager
            ->expects(self::once())
            ->method('createSession')
            ->with(
                $user,
                SessionStatus::PIN_VERIFICATION_REQUIRED,
                '127.0.0.1',
                'PHPUnit',
            )
            ->willReturn($session);

        $this->jwtTokenManager
            ->expects(self::once())
            ->method('createFromPayload')
            ->with(
                $user,
                self::callback(static function (array $payload): bool {
                    return $payload['status'] === SessionStatus::PIN_VERIFICATION_REQUIRED->value
                        && $payload['has_pin'] === true
                        && is_string($payload['jti'])
                        && $payload['jti'] !== '';
                }),
            )
            ->willReturn('partial-jwt-token');

        $this->sessionManager
            ->expects(self::once())
            ->method('assignTokenToSession')
            ->with($session, 'partial-jwt-token');

        $this->authTokenService->createPartialToken(
            user: $user,
            status: SessionStatus::PIN_VERIFICATION_REQUIRED,
            message: ResponseMessage::LOGIN_SUCCESS,
            request: $this->request,
        );

        self::assertSame('partial-jwt-token', $this->request->attributes->get('_partial_auth_token'));
    }

    public function testCreateAuthenticatedTokenMarksSessionAssignsRefreshTokenAndSetsRequestAttributes(): void
    {
        $user = $this->createUser();
        $session = $this->createSession($user, SessionStatus::PIN_SETUP_REQUIRED);

        $this->jwtTokenManager
            ->expects(self::once())
            ->method('createFromPayload')
            ->with(
                $user,
                self::callback(static function (array $payload): bool {
                    return $payload['session_id'] === ''
                        && $payload['status'] === SessionStatus::AUTHENTICATED->value
                        && $payload['has_pin'] === false
                        && is_string($payload['jti'])
                        && $payload['jti'] !== '';
                }),
            )
            ->willReturn('authenticated-jwt-token');

        $this->sessionManager
            ->expects(self::once())
            ->method('markSessionAsAuthenticated')
            ->with($session, 'authenticated-jwt-token');

        $this->sessionManager
            ->expects(self::once())
            ->method('assignRefreshTokenToSession')
            ->with(
                $session,
                self::callback(static fn (string $refreshToken): bool => strlen($refreshToken) === 128),
            );

        $response = $this->authTokenService->createAuthenticatedToken(
            user: $user,
            session: $session,
            message: ResponseMessage::AUTH_COMPLETE,
        );

        self::assertInstanceOf(AuthTokenResponse::class, $response);
        self::assertSame('authenticated-jwt-token', $this->request->attributes->get('_auth_token'));
        self::assertIsString($this->request->attributes->get('_refresh_token'));
        self::assertSame(128, strlen((string) $this->request->attributes->get('_refresh_token')));
        self::assertTrue($this->request->attributes->get('_expire_partial'));
        self::assertFalse($this->request->attributes->has('_partial_auth_token'));
    }

    public function testRefreshAuthenticatedTokenRotatesTokensAndSetsRequestAttributes(): void
    {
        $user = $this->createUser();
        $session = $this->createSession($user, SessionStatus::AUTHENTICATED);

        $this->jwtTokenManager
            ->expects(self::once())
            ->method('createFromPayload')
            ->with(
                $user,
                self::callback(static function (array $payload): bool {
                    return $payload['session_id'] === ''
                        && $payload['status'] === SessionStatus::AUTHENTICATED->value
                        && $payload['has_pin'] === false
                        && is_string($payload['jti'])
                        && $payload['jti'] !== '';
                }),
            )
            ->willReturn('refreshed-jwt-token');

        $this->sessionManager
            ->expects(self::once())
            ->method('rotateTokens')
            ->with(
                $session,
                'refreshed-jwt-token',
                self::callback(static fn (string $refreshToken): bool => strlen($refreshToken) === 128),
            );

        $response = $this->authTokenService->refreshAuthenticatedToken($session);

        self::assertInstanceOf(AuthTokenResponse::class, $response);
        self::assertSame('refreshed-jwt-token', $this->request->attributes->get('_auth_token'));
        self::assertIsString($this->request->attributes->get('_refresh_token'));
        self::assertSame(128, strlen((string) $this->request->attributes->get('_refresh_token')));
        self::assertFalse($this->request->attributes->has('_expire_partial'));
        self::assertFalse($this->request->attributes->has('_partial_auth_token'));
    }

    public function testGeneratedRefreshTokenIsDifferentOnConsecutiveAuthenticatedTokens(): void
    {
        $user = $this->createUser();
        $session = $this->createSession($user, SessionStatus::PIN_SETUP_REQUIRED);

        $this->jwtTokenManager
            ->method('createFromPayload')
            ->willReturn('authenticated-jwt-token');

        $refreshTokens = [];

        $this->sessionManager
            ->expects(self::exactly(2))
            ->method('assignRefreshTokenToSession')
            ->willReturnCallback(static function (Session $session, string $refreshToken) use (&$refreshTokens): void {
                $refreshTokens[] = $refreshToken;
            });

        $this->authTokenService->createAuthenticatedToken(
            user: $user,
            session: $session,
            message: ResponseMessage::AUTH_COMPLETE,
        );

        $this->request->attributes->remove('_auth_token');
        $this->request->attributes->remove('_refresh_token');
        $this->request->attributes->remove('_expire_partial');

        $this->authTokenService->createAuthenticatedToken(
            user: $user,
            session: $session,
            message: ResponseMessage::AUTH_COMPLETE,
        );

        self::assertCount(2, $refreshTokens);
        self::assertNotSame($refreshTokens[0], $refreshTokens[1]);
    }

    private function createUser(): User
    {
        return new User(
            email: 'user@example.com',
            username: 'user',
            password: 'hashed-password',
        );
    }

    private function createSession(User $user, SessionStatus $status): Session
    {
        return new Session(
            user: $user,
            tokenHash: 'initial-token-hash',
            expiresAt: (new DateTimeImmutable())->modify('+1 hour'),
            status: $status,
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
        );
    }
}