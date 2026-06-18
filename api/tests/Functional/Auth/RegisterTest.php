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

namespace App\Tests\Functional\Auth;

use App\Auth\Factory\CookieFactory;
use App\Entity\User;
use App\Enum\ResponseMessage;
use App\Enum\SessionStatus;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class RegisterTest extends FunctionalTestCase
{
    public function testRegisterCreatesUserSessionAndPartialAccessToken(): void
    {
        $response = $this->postJson('/api/register', [
            'username' => 'dawid',
            'email' => 'dawid@example.com',
            'password' => 'Password123!',
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(SessionStatus::PIN_SETUP_REQUIRED->value, $data['status']);
        self::assertSame(ResponseMessage::REGISTER_SUCCESS->value, $data['message']);
        self::assertSame('dawid@example.com', $data['user']['email']);
        self::assertSame('dawid', $data['user']['username']);
        self::assertFalse($data['user']['hasPin']);
        self::assertSame(['ROLE_USER'], $data['user']['roles']);

        $this->assertAuthTokensAreNotExposedInBody($data);

        $partialCookie = $this->assertCookieExists(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        self::assertTrue($partialCookie->isHttpOnly());

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $user = $this->findUserByEmail('dawid@example.com');

        self::assertInstanceOf(User::class, $user);
        self::assertSame('dawid', $user->getUsername());
        self::assertNotSame('Password123!', $user->getPassword());
        self::assertNull($user->getPin());

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::PIN_SETUP_REQUIRED, $sessions[0]->getStatus());
        self::assertNotSame('', $sessions[0]->getTokenHash());
        self::assertNull($sessions[0]->getRefreshTokenHash());
    }

    public function testRegisterWithDuplicatedEmailReturnsConflictAndDoesNotCreateSecondUser(): void
    {
        $this->createUser(
            email: 'dawid@example.com',
            username: 'existing-user',
            plainPassword: 'Password123!',
        );

        $response = $this->postJson('/api/register', [
            'username' => 'new-user',
            'email' => 'dawid@example.com',
            'password' => 'Password123!',
        ]);

        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertArrayHasKey('message', $data);

        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $users = $this->entityManager
            ->getRepository(User::class)
            ->findBy(['email' => 'dawid@example.com']);

        self::assertCount(1, $users);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPayloadProvider')]
    public function testRegisterWithInvalidPayloadReturnsValidationError(array $payload): void
    {
        $response = $this->postJson('/api/register', $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $users = $this->entityManager
            ->getRepository(User::class)
            ->findAll();

        self::assertCount(0, $users);
    }

    public function testRegisterWithMalformedJsonReturnsBadRequest(): void
    {
        $response = $this->postMalformedJson('/api/register', '{"email": "broken"');

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

        yield 'missing username' => [
            'payload' => [
                'email' => 'dawid@example.com',
                'password' => 'Password123!',
            ],
        ];

        yield 'missing email' => [
            'payload' => [
                'username' => 'dawid',
                'password' => 'Password123!',
            ],
        ];

        yield 'missing password' => [
            'payload' => [
                'username' => 'dawid',
                'email' => 'dawid@example.com',
            ],
        ];

        yield 'blank username' => [
            'payload' => [
                'username' => '',
                'email' => 'dawid@example.com',
                'password' => 'Password123!',
            ],
        ];

        yield 'invalid email' => [
            'payload' => [
                'username' => 'dawid',
                'email' => 'not-an-email',
                'password' => 'Password123!',
            ],
        ];

        yield 'too short password' => [
            'payload' => [
                'username' => 'dawid',
                'email' => 'dawid@example.com',
                'password' => 'short',
            ],
        ];
    }
}
