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

use App\Entity\User;
use App\Controller\PinController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[CoversClass(PinController::class)]
#[Medium]
final class PinControllerTest extends WebTestCase
{
    private function createAuthenticatedClientAndUser(): array
    {
        /** @var KernelBrowser $client */
        $client = PinControllerTest::createClient();
        /** @var ContainerInterface $container */
        $container = PinControllerTest::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        $user = new User(
            'tester_' . uniqid(),
            uniqid('user_') . '@example.com',
            'dummy_pass123!'
        );
        $entityManager->persist($user);
        $entityManager->flush();
        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $container->get('lexik_jwt_authentication.jwt_manager');
        /** @var string $token */
        $token = $jwtManager->create($user);
        $client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));

        return [$client, $user, $entityManager];
    }
    #[Test]
    public function it_returns_422_on_invalid_dto_validation(): void
    {
        /** @var KernelBrowser $client */
        [$client] = $this->createAuthenticatedClientAndUser();
        $client->request(
            'POST',
            '/api/pin/setup',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['pin' => '123'])
        );
        $this->assertResponseStatusCodeSame(422);
    }
    #[Test]
    public function it_sets_up_pin_successfully_for_authorized_user(): void
    {
        /** @var KernelBrowser $client */
        /** @var User $user */
        [$client, $user] = $this->createAuthenticatedClientAndUser();
        $client->request(
            'POST',
            '/api/pin/setup',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['pin' => '123456'])
        );
        $this->assertResponseIsSuccessful();
        /** @var string $responseContent */
        $responseContent = $client->getResponse()->getContent();
        $this->assertStringContainsString('PIN successfully set up.', $responseContent);
    }
    #[Test]
    public function it_blocks_unauthorized_access(): void
    {
        /** @var KernelBrowser $client */
        $client = PinControllerTest::createClient();
        $client->request(
            'POST',
            '/api/pin/setup',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['pin' => '123456'])
        );
        $this->assertResponseStatusCodeSame(401);
    }
    #[Test]
    public function it_verifies_pin_successfully(): void
    {
        /** @var KernelBrowser $client */
        /** @var User $user */
        /** @var EntityManagerInterface $entityManager */
        [$client, $user, $entityManager] = $this->createAuthenticatedClientAndUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));
        $entityManager->flush();
        $client->request(
            'POST',
            '/api/pin/verify',
            [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['pin' => '123456'])
        );
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('PIN verified successfully.', $client->getResponse()->getContent());
    }
    #[Test]
    public function it_blocks_verification_after_3_failed_attempts_over_http(): void
    {
        /** @var KernelBrowser $client */
        /** @var User $user */
        /** @var EntityManagerInterface $entityManager */
        [$client, $user, $entityManager] = $this->createAuthenticatedClientAndUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));
        $entityManager->flush();
        for ($i = 0; $i < 3; $i++) {
            $client->request(
                'POST',
                '/api/pin/verify',
                [], [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['pin' => 'wrong_pin'])
            );
            $this->assertResponseStatusCodeSame(403);
        }
        $client->request(
            'POST',
            '/api/pin/verify',
            [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['pin' => '123456'])
        );
        $this->assertResponseStatusCodeSame(403);
        /** @var EntityManagerInterface $freshEntityManager */
        $freshEntityManager = PinControllerTest::getContainer()->get('doctrine')->getManager();
        /** @var User|null $updatedUser */
        $updatedUser = $freshEntityManager->getRepository(User::class)->find($user->getId());
        $this->assertNotNull($updatedUser->getPinLockedUntil());
    }
    #[Test]
    public function it_changes_pin_over_http(): void
    {
        /** @var KernelBrowser $client */
        /** @var User $user */
        /** @var EntityManagerInterface $entityManager */
        [$client, $user, $entityManager] = $this->createAuthenticatedClientAndUser();
        $user->setPin(password_hash('123456', PASSWORD_DEFAULT));
        $entityManager->flush();
        $client->request(
            'PUT',
            '/api/pin/change',
            [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['oldPin' => '123456', 'newPin' => '654321'])
        );
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('PIN successfully changed.', $client->getResponse()->getContent());
        /** @var EntityManagerInterface $freshEntityManager */
        $freshEntityManager = PinControllerTest::getContainer()->get('doctrine')->getManager();
        /** @var User|null $updatedUser */
        $updatedUser = $freshEntityManager->getRepository(User::class)->find($user->getId());
        $this->assertTrue(password_verify('654321', (string) $updatedUser->getPin()));
    }
}