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
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ListSessionsTest extends FunctionalTestCase
{
    public function testListSessionsReturnsUnauthorizedForGuest(): void
    {
        $response = $this->getJson('/api/auth/sessions');

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testListSessionsReturnsAuthenticatedUserSessions(): void
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

        $response = $this->getJson('/api/auth/sessions');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertIsArray($data);
        self::assertCount(1, $data);
        self::assertArrayHasKey('id', $data[0]);
        self::assertArrayHasKey('ipAddress', $data[0]);
        self::assertArrayHasKey('userAgent', $data[0]);
        self::assertArrayHasKey('createdAt', $data[0]);
        self::assertArrayHasKey('expiresAt', $data[0]);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $sessions = $this->findSessionsForUser($user);

        self::assertCount(1, $sessions);
        self::assertSame((string) $sessions[0]->getId(), $data[0]['id']);
        self::assertSame($sessions[0]->getIpAddress(), $data[0]['ipAddress']);
        self::assertSame($sessions[0]->getUserAgent(), $data[0]['userAgent']);
    }

    private function getJson(string $uri): Response
    {
        $this->client->request(
            method: 'GET',
            uri: $uri,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $this->clientIp,
            ],
        );

        return $this->client->getResponse();
    }
}
