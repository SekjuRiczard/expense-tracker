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

use App\Entity\User;
use App\Enum\SessionStatus;
use App\Enum\UserRole;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class MeTest extends FunctionalTestCase
{
    public function testMeReturnsUnauthorizedForGuest(): void
    {
        $response = $this->getJson('/api/me');

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testMeReturnsAuthenticatedUserWithDefaultRole(): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $this->registerAndSetupPin($email, $username);

        $response = $this->getJson('/api/me');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame(SessionStatus::AUTHENTICATED->value, $data['status']);
        self::assertSame($email, $data['user']['email']);
        self::assertSame($username, $data['user']['username']);
        self::assertTrue($data['user']['hasPin']);
        self::assertSame(['ROLE_USER'], $data['user']['roles']);
    }

    public function testMeExposesAdminRoleSoFrontendCanRevealDemoDataTools(): void
    {
        $email = $this->uniqueEmail('admin');
        $username = $this->uniqueUsername('admin');

        $this->registerAndSetupPin($email, $username, UserRole::ADMIN);

        $response = $this->getJson('/api/me');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertContains(UserRole::ADMIN->value, $data['user']['roles']);
        self::assertContains(UserRole::USER->value, $data['user']['roles']);
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

    private function registerAndSetupPin(
        string $email,
        string $username,
        ?UserRole $role = null,
    ): void {
        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        if (null !== $role) {
            $user = $this->findUserByEmail($email);

            self::assertInstanceOf(User::class, $user);

            $user->setRoles($role);
            $this->entityManager->flush();
        }

        $this->postJson('/api/pin/setup', [
            'pin' => '123456',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
