<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Auth\Factory\CookieFactory;
use App\Enum\ResponseMessage;
use App\Enum\SessionStatus;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class LoginTest extends FunctionalTestCase
{
    public function testLoginUserWithoutPinReturnsPartialTokenForPinSetup(): void
    {
        $user = $this->createUser(
            email: 'dawid@example.com',
            username: 'dawid',
            plainPassword: 'Password123!',
            pinHash: null,
        );

        $response = $this->postJson('/api/login', [
            'email' => 'dawid@example.com',
            'password' => 'Password123!',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(SessionStatus::PIN_SETUP_REQUIRED->value, $data['status']);
        self::assertSame(ResponseMessage::PIN_SETUP_REQUIRED->value, $data['message']);
        self::assertSame('dawid@example.com', $data['user']['email']);
        self::assertSame('dawid', $data['user']['username']);
        self::assertFalse($data['user']['hasPin']);

        $this->assertAuthTokensAreNotExposedInBody($data);

        $partialCookie = $this->assertCookieExists(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        self::assertTrue($partialCookie->isHttpOnly());

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $this->entityManager->refresh($user);

        self::assertNotNull($user->getLastLoginAt());

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::PIN_SETUP_REQUIRED, $sessions[0]->getStatus());
        self::assertNotSame('', $sessions[0]->getTokenHash());
        self::assertNull($sessions[0]->getRefreshTokenHash());
    }

    public function testLoginUserWithPinReturnsPartialTokenForPinVerification(): void
    {
        $user = $this->createUser(
            email: 'dawid@example.com',
            username: 'dawid',
            plainPassword: 'Password123!',
            pinHash: 'already-hashed-pin',
        );

        $response = $this->postJson('/api/login', [
            'email' => 'dawid@example.com',
            'password' => 'Password123!',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(SessionStatus::PIN_VERIFICATION_REQUIRED->value, $data['status']);
        self::assertSame(ResponseMessage::LOGIN_SUCCESS->value, $data['message']);
        self::assertSame('dawid@example.com', $data['user']['email']);
        self::assertSame('dawid', $data['user']['username']);
        self::assertTrue($data['user']['hasPin']);

        $this->assertAuthTokensAreNotExposedInBody($data);

        $partialCookie = $this->assertCookieExists(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        self::assertTrue($partialCookie->isHttpOnly());

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $this->entityManager->refresh($user);

        self::assertNotNull($user->getLastLoginAt());

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::PIN_VERIFICATION_REQUIRED, $sessions[0]->getStatus());
        self::assertNotSame('', $sessions[0]->getTokenHash());
        self::assertNull($sessions[0]->getRefreshTokenHash());
    }

    public function testLoginWithUnknownEmailReturnsUnauthorizedAndDoesNotCreateSession(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'missing@example.com',
            'password' => 'Password123!',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertArrayHasKey('message', $data);

        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);
    }

    public function testLoginWithWrongPasswordReturnsUnauthorizedAndDoesNotCreateSession(): void
    {
        $user = $this->createUser(
            email: 'dawid@example.com',
            username: 'dawid',
            plainPassword: 'Password123!',
        );

        $response = $this->postJson('/api/login', [
            'email' => 'dawid@example.com',
            'password' => 'WrongPassword123!',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertArrayHasKey('message', $data);

        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(0, $sessions);
    }

    public function testLoginInactiveUserReturnsUnauthorizedAndDoesNotCreateSession(): void
    {
        $user = $this->createUser(
            email: 'dawid@example.com',
            username: 'dawid',
            plainPassword: 'Password123!',
            isActive: false,
        );

        $response = $this->postJson('/api/login', [
            'email' => 'dawid@example.com',
            'password' => 'Password123!',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(0, $sessions);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPayloadProvider')]
    public function testLoginWithInvalidPayloadReturnsValidationError(array $payload): void
    {
        $response = $this->postJson('/api/login', $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);
    }

    public function testLoginWithMalformedJsonReturnsBadRequest(): void
    {
        $response = $this->postMalformedJson('/api/login', '{"email": "broken"');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>}>
     */
    public static function invalidPayloadProvider(): iterable
    {
        yield 'empty payload' => [
            'payload' => [],
        ];

        yield 'missing email' => [
            'payload' => [
                'password' => 'Password123!',
            ],
        ];

        yield 'missing password' => [
            'payload' => [
                'email' => 'dawid@example.com',
            ],
        ];

        yield 'blank email' => [
            'payload' => [
                'email' => '',
                'password' => 'Password123!',
            ],
        ];

        yield 'blank password' => [
            'payload' => [
                'email' => 'dawid@example.com',
                'password' => '',
            ],
        ];

        yield 'invalid email' => [
            'payload' => [
                'email' => 'not-an-email',
                'password' => 'Password123!',
            ],
        ];
    }
}