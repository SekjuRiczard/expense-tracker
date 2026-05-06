<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Auth\Service\AuthTokenService;
use App\Entity\Session;
use App\Entity\User;
use App\Enum\AuthStage;
use App\Enum\SessionStatus;
use App\Session\Service\SessionManagerInterface;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[Small]
final class AuthTokenServiceTest extends TestCase
{
    #[Test]
    public function create_partial_token_creates_session_and_returns_token_response(): void
    {
        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $sessionManager = $this->createMock(SessionManagerInterface::class);

        $user = $this->createUser(hasPin: false);
        $request = new Request();
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $request->headers->set('User-Agent', 'PHPUnit');

        $session = $this->createSession(
            user: $user,
            status: SessionStatus::PIN_SETUP_REQUIRED,
        );

        $sessionManager
            ->expects(self::once())
            ->method('createSession')
            ->with(
                $user,
                SessionStatus::PIN_SETUP_REQUIRED,
                '127.0.0.1',
                'PHPUnit',
            )
            ->willReturn($session);

        $jwtManager
            ->expects(self::once())
            ->method('createFromPayload')
            ->with(
                $user,
                self::callback(static function (array $payload): bool {
                    return $payload['auth_stage'] === 'pin_setup_required'
                        && $payload['has_pin'] === false
                        && array_key_exists('session_id', $payload);
                }),
            )
            ->willReturn('partial-token');

        $sessionManager
            ->expects(self::once())
            ->method('assignTokenToSession')
            ->with($session, 'partial-token');

        $service = new AuthTokenService(
            jwtTokenManager: $jwtManager,
            sessionManager: $sessionManager,
        );

        $response = $service->createPartialToken(
            user: $user,
            status: SessionStatus::PIN_SETUP_REQUIRED,
            request: $request,
        );

        self::assertSame('partial-token', $response->token);
        self::assertSame(AuthStage::PIN_SETUP_REQUIRED, $response->authStage);
        self::assertSame($session, $response->session);
        self::assertSame([
            'token' => 'partial-token',
            'status' => 'pin_setup_required',
        ], $response->toArray());
    }

    #[Test]
    public function create_authenticated_token_marks_session_as_authenticated(): void
    {
        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $sessionManager = $this->createMock(SessionManagerInterface::class);

        $user = $this->createUser(hasPin: true);
        $session = $this->createSession(
            user: $user,
            status: SessionStatus::PIN_VERIFICATION_REQUIRED,
        );

        $jwtManager
            ->expects(self::once())
            ->method('createFromPayload')
            ->with(
                $user,
                self::callback(static function (array $payload): bool {
                    return $payload['auth_stage'] === 'authenticated'
                        && $payload['has_pin'] === true
                        && array_key_exists('session_id', $payload);
                }),
            )
            ->willReturn('full-token');

        $sessionManager
            ->expects(self::once())
            ->method('markSessionAsAuthenticated')
            ->with($session, 'full-token');

        $capturedRefreshToken = null;

        $sessionManager
            ->expects(self::once())
            ->method('assignRefreshTokenToSession')
            ->with(
                $session,
                self::callback(static function (string $refreshToken) use (&$capturedRefreshToken): bool {
                    $capturedRefreshToken = $refreshToken;

                    return strlen($refreshToken) === 128
                        && ctype_xdigit($refreshToken);
                }),
            );

        $service = new AuthTokenService(
            jwtTokenManager: $jwtManager,
            sessionManager: $sessionManager,
        );

        $response = $service->createAuthenticatedToken(
            user: $user,
            session: $session,
        );

        self::assertSame('full-token', $response->token);
        self::assertSame(AuthStage::AUTHENTICATED, $response->authStage);
        self::assertSame($session, $response->session);
        self::assertSame($capturedRefreshToken, $response->refreshToken);

        self::assertSame([
            'token' => 'full-token',
            'status' => 'authenticated',
            'refreshToken' => $capturedRefreshToken,
        ], $response->toArray());
    }

    private function createUser(bool $hasPin): User
    {
        $user = new User(
            email: 'john@example.com',
            username: 'john',
            password: 'hashed-password',
        );

        if ($hasPin) {
            $user->setPin(password_hash('123456', PASSWORD_DEFAULT));
        }

        return $user;
    }

    private function createSession(User $user, SessionStatus $status): Session
    {
        return new Session(
            user: $user,
            tokenHash: hash('sha256', 'token'),
            expiresAt: (new DateTimeImmutable())->modify('+1 hour'),
            status: $status,
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
        );
    }
}