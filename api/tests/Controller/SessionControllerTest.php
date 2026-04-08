<?php

/*
 * This file is part of the Expense Tracker.
 *
 * (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Session;
use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class SessionControllerTest extends WebTestCase
{
    #[Test]
    public function it_returns_unauthorized_for_guest(): void
    {
        /** @var KernelBrowser $client */
        $client = SessionControllerTest::createClient();
        $client->request('GET', '/api/auth/sessions');
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    #[Test]
    public function it_denies_access_when_deleting_other_user_session(): void
    {
        /** @var KernelBrowser $client */
        $client = SessionControllerTest::createClient();
        /** @var Session $session */
        $session = new Session($this->createUser('user2@example.com'), 'hash2', new DateTimeImmutable('+1 hour'));
        SessionControllerTest::getContainer()->get('doctrine.orm.entity_manager')->persist($session);
        SessionControllerTest::getContainer()->get('doctrine.orm.entity_manager')->flush();
        $client->loginUser($this->createUser('user1@example.com'));
        $client->request('DELETE', '/api/auth/sessions/' . $session->getId());
        $this->assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    #[Test]
    public function it_successfully_removes_own_session(): void
    {
        /** @var KernelBrowser $client */
        $client = SessionControllerTest::createClient();
        /** @var User $user */
        $user = $this->createUser('user3@example.com');
        /** @var Session $session */
        $session = new Session($user, 'hash3', new DateTimeImmutable('+1 hour'));
        SessionControllerTest::getContainer()->get('doctrine.orm.entity_manager')->persist($session);
        SessionControllerTest::getContainer()->get('doctrine.orm.entity_manager')->flush();
        $client->loginUser($user);
        $client->request('DELETE', '/api/auth/sessions/' . $session->getId());
        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    private function createUser(string $email): User
    {
        /** @var User $user */
        $user = new User($email, 'user', 'pass');
        SessionControllerTest::getContainer()->get('doctrine.orm.entity_manager')->persist($user);
        SessionControllerTest::getContainer()->get('doctrine.orm.entity_manager')->flush();

        return $user;
    }
}
