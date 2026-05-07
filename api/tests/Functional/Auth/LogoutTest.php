<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Auth\Factory\CookieFactory;
use App\Entity\User;
use App\Enum\SessionStatus;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class LogoutTest extends FunctionalTestCase
{
    public function testLogoutWithAuthenticatedSessionRevokesSessionAndClearsCookies(): void
    {
        [$email] = $this->authenticateUserByPinSetup();

        $response = $this->postJson('/api/logout', []);

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCookieExpired(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieExpired(CookieFactory::REFRESH_TOKEN_COOKIE);
        $this->assertCookieExpired(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::REVOKED, $sessions[0]->getStatus());
        self::assertTrue($sessions[0]->isRevoked());
        self::assertNotNull($sessions[0]->getRevokedAt());
    }

    public function testRefreshAfterLogoutReturnsUnauthorized(): void
    {
        $this->authenticateUserByPinSetup();

        $this->postJson('/api/logout', []);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $response = $this->postJson('/api/token/refresh', []);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testLogoutWithoutTokenReturnsUnauthorized(): void
    {
        $response = $this->postJson('/api/logout', []);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
    }

    public function testLogoutWithPartialAccessTokenReturnsNoContentAndClearsCookies(): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertCookieExists(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $response = $this->postJson('/api/logout', []);

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCookieExpired(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieExpired(CookieFactory::REFRESH_TOKEN_COOKIE);
        $this->assertCookieExpired(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::REVOKED, $sessions[0]->getStatus());
        self::assertTrue($sessions[0]->isRevoked());
        self::assertNotNull($sessions[0]->getRevokedAt());
    }

    public function testLogoutTwiceReturnsUnauthorizedSecondTime(): void
    {
        $this->authenticateUserByPinSetup();

        $this->postJson('/api/logout', []);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $response = $this->postJson('/api/logout', []);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function authenticateUserByPinSetup(): array
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->postJson('/api/pin/setup', [
            'pin' => '123456',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCookieExists(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieExists(CookieFactory::REFRESH_TOKEN_COOKIE);
        $this->assertCookieExpired(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);

        return [$email, $username];
    }
}