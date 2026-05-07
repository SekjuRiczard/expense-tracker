<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Auth\Factory\CookieFactory;
use App\Entity\User;
use App\Enum\ResponseMessage;
use App\Enum\SessionStatus;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class PinVerifyTest extends FunctionalTestCase
{
    public function testVerifyPinWithPartialAccessTokenAuthenticatesUserAndSetsFullCookies(): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $user = $this->createUser(
            email: $email,
            username: $username,
            plainPassword: 'Password123!',
            pinHash: password_hash('123456', PASSWORD_DEFAULT),
        );

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $loginData = $this->jsonResponse();

        self::assertSame(SessionStatus::PIN_VERIFICATION_REQUIRED->value, $loginData['status']);

        $partialCookie = $this->assertCookieExists(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        self::assertTrue($partialCookie->isHttpOnly());

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $response = $this->postJson('/api/pin/verify', [
            'pin' => '123456',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(SessionStatus::AUTHENTICATED->value, $data['status']);
        self::assertSame(ResponseMessage::AUTH_COMPLETE->value, $data['message']);
        self::assertSame($email, $data['user']['email']);
        self::assertSame($username, $data['user']['username']);
        self::assertTrue($data['user']['hasPin']);

        $this->assertAuthTokensAreNotExposedInBody($data);

        $accessCookie = $this->assertCookieExists(CookieFactory::ACCESS_TOKEN_COOKIE);
        $refreshCookie = $this->assertCookieExists(CookieFactory::REFRESH_TOKEN_COOKIE);
        $expiredPartialCookie = $this->assertCookieExpired(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);

        self::assertTrue($accessCookie->isHttpOnly());
        self::assertTrue($refreshCookie->isHttpOnly());
        self::assertTrue($expiredPartialCookie->isHttpOnly());

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);
        self::assertTrue(password_verify('123456', (string) $user->getPin()));

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::AUTHENTICATED, $sessions[0]->getStatus());
        self::assertNotSame('', $sessions[0]->getTokenHash());
        self::assertNotNull($sessions[0]->getRefreshTokenHash());
        self::assertNotNull($sessions[0]->getRefreshTokenExpiresAt());
        self::assertNotNull($sessions[0]->getAuthenticatedAt());
    }

    public function testVerifyPinWithoutTokenReturnsUnauthorized(): void
    {
        $response = $this->postJson('/api/pin/verify', [
            'pin' => '123456',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertArrayHasKey('message', $data);

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
    }

    public function testVerifyPinWithInvalidPinReturnsForbiddenAndDoesNotAuthenticateSession(): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $user = $this->createUser(
            email: $email,
            username: $username,
            plainPassword: 'Password123!',
            pinHash: password_hash('123456', PASSWORD_DEFAULT),
        );

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->postJson('/api/pin/verify', [
            'pin' => '654321',
        ]);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertSame('Invalid PIN.', $data['message']);

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::PIN_VERIFICATION_REQUIRED, $sessions[0]->getStatus());
        self::assertNull($sessions[0]->getRefreshTokenHash());
        self::assertNull($sessions[0]->getAuthenticatedAt());
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPayloadProvider')]
    public function testVerifyPinWithInvalidPayloadReturnsValidationError(array $payload): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $user = $this->createUser(
            email: $email,
            username: $username,
            plainPassword: 'Password123!',
            pinHash: password_hash('123456', PASSWORD_DEFAULT),
        );

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->postJson('/api/pin/verify', $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::PIN_VERIFICATION_REQUIRED, $sessions[0]->getStatus());
        self::assertNull($sessions[0]->getRefreshTokenHash());
    }

    public function testVerifyPinWithMalformedJsonReturnsBadRequest(): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $user = $this->createUser(
            email: $email,
            username: $username,
            plainPassword: 'Password123!',
            pinHash: password_hash('123456', PASSWORD_DEFAULT),
        );

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->postMalformedJson('/api/pin/verify', '{"pin": "123456"');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::PIN_VERIFICATION_REQUIRED, $sessions[0]->getStatus());
    }

    public function testVerifyPinWithPinSetupRequiredSessionReturnsForbidden(): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $this->postJson('/api/pin/verify', [
            'pin' => '123456',
        ]);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);
        self::assertNull($user->getPin());

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::PIN_SETUP_REQUIRED, $sessions[0]->getStatus());
        self::assertNull($sessions[0]->getRefreshTokenHash());
    }

    public function testVerifyPinSecondTimeReturnsForbiddenBecauseSessionIsAlreadyAuthenticated(): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $this->createUser(
            email: $email,
            username: $username,
            plainPassword: 'Password123!',
            pinHash: password_hash('123456', PASSWORD_DEFAULT),
        );

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->postJson('/api/pin/verify', [
            'pin' => '123456',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->postJson('/api/pin/verify', [
            'pin' => '123456',
        ]);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::AUTHENTICATED, $sessions[0]->getStatus());
        self::assertNotNull($sessions[0]->getRefreshTokenHash());
    }

    public function testVerifyPinLocksUserAfterThreeInvalidAttempts(): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $user = $this->createUser(
            email: $email,
            username: $username,
            plainPassword: 'Password123!',
            pinHash: password_hash('123456', PASSWORD_DEFAULT),
        );

        $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        for ($attempt = 1; $attempt <= 3; ++$attempt) {
            $this->postJson('/api/pin/verify', [
                'pin' => '654321',
            ]);

            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);
        self::assertNotNull($user->getPinLockedUntil());

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::PIN_VERIFICATION_REQUIRED, $sessions[0]->getStatus());
        self::assertNull($sessions[0]->getRefreshTokenHash());
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>}>
     */
    public static function invalidPayloadProvider(): iterable
    {
        yield 'empty payload' => [
            'payload' => [],
        ];

        yield 'missing pin' => [
            'payload' => [
                'other' => '123456',
            ],
        ];

        yield 'blank pin' => [
            'payload' => [
                'pin' => '',
            ],
        ];

        yield 'too short pin' => [
            'payload' => [
                'pin' => '12345',
            ],
        ];
    }
}