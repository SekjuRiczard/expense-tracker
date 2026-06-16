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
use App\Enum\SessionStatus;
use App\Session\Service\SessionManagerInterface;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class RefreshTokenTest extends FunctionalTestCase
{
    public function testRefreshWithoutRefreshTokenReturnsUnauthorized(): void
    {
        $response = $this->postJson('/api/token/refresh', []);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
    }

    public function testRefreshWithInvalidRefreshTokenReturnsUnauthorized(): void
    {
        $this->setBrowserCookie(CookieFactory::REFRESH_TOKEN_COOKIE, 'invalid-refresh-token');

        $response = $this->postJson('/api/token/refresh', []);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        // A present-but-invalid refresh token clears all auth cookies so the
        // stale state cannot block a fresh login.
        $this->assertCookieExpired(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieExpired(CookieFactory::REFRESH_TOKEN_COOKIE);
        $this->assertCookieExpired(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
    }

    public function testRefreshWithValidRefreshTokenSetsNewAccessAndRefreshCookies(): void
    {
        [$email] = $this->authenticateUserByPinSetup();

        $oldAccessToken = $this->getResponseCookieValue(CookieFactory::ACCESS_TOKEN_COOKIE);
        $oldRefreshToken = $this->getResponseCookieValue(CookieFactory::REFRESH_TOKEN_COOKIE);

        $response = $this->postJson('/api/token/refresh', []);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(SessionStatus::AUTHENTICATED->value, $data['status']);
        $this->assertAuthTokensAreNotExposedInBody($data);

        $newAccessCookie = $this->assertCookieExists(CookieFactory::ACCESS_TOKEN_COOKIE);
        $newRefreshCookie = $this->assertCookieExists(CookieFactory::REFRESH_TOKEN_COOKIE);

        self::assertTrue($newAccessCookie->isHttpOnly());
        self::assertTrue($newRefreshCookie->isHttpOnly());

        self::assertNotSame($oldAccessToken, $newAccessCookie->getValue());
        self::assertNotSame($oldRefreshToken, $newRefreshCookie->getValue());

        $this->assertCookieMissing(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame(SessionStatus::AUTHENTICATED, $sessions[0]->getStatus());
        self::assertNotNull($sessions[0]->getRefreshTokenHash());
        self::assertNotNull($sessions[0]->getRefreshTokenExpiresAt());
        self::assertFalse($sessions[0]->isRevoked());
    }

    public function testOldRefreshTokenCannotBeUsedAfterRotation(): void
    {
        $this->authenticateUserByPinSetup();

        $oldRefreshToken = $this->getResponseCookieValue(CookieFactory::REFRESH_TOKEN_COOKIE);

        $this->postJson('/api/token/refresh', []);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->setBrowserCookie(CookieFactory::REFRESH_TOKEN_COOKIE, $oldRefreshToken);

        $response = $this->postJson('/api/token/refresh', []);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testNewRefreshTokenCanBeUsedAfterRotation(): void
    {
        $this->authenticateUserByPinSetup();

        $this->postJson('/api/token/refresh', []);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $firstRotatedRefreshToken = $this->getResponseCookieValue(CookieFactory::REFRESH_TOKEN_COOKIE);

        $this->postJson('/api/token/refresh', []);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $secondRotatedRefreshToken = $this->getResponseCookieValue(CookieFactory::REFRESH_TOKEN_COOKIE);

        self::assertNotSame($firstRotatedRefreshToken, $secondRotatedRefreshToken);
    }

    public function testRefreshForRevokedSessionReturnsUnauthorized(): void
    {
        [$email] = $this->authenticateUserByPinSetup();

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);

        /** @var SessionManagerInterface $sessionManager */
        $sessionManager = static::getContainer()->get(SessionManagerInterface::class);
        $sessionManager->revokeSession($sessions[0]);

        $response = $this->postJson('/api/token/refresh', []);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testRefreshWithPartialAccessOnlyReturnsUnauthorized(): void
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
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);

        $response = $this->postJson('/api/token/refresh', []);

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
