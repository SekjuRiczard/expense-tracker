<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Auth\Factory\CookieFactory;
use App\Entity\Session;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\BrowserKit\Cookie as BrowserKitCookie;

abstract class FunctionalTestCase extends WebTestCase
{
    protected AbstractBrowser $client;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->clientIp = sprintf(
            '127.0.%d.%d',
            random_int(1, 254),
            random_int(1, 254),
        );

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function postJson(string $uri, array $payload): Response
    {
        $this->client->request(
            method: 'POST',
            uri: $uri,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $this->clientIp,
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        return $this->client->getResponse();
    }

    protected function postMalformedJson(string $uri, string $content): Response
    {
        $this->client->request(
            method: 'POST',
            uri: $uri,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $this->clientIp,
            ],
            content: $content,
        );

        return $this->client->getResponse();
    }

    /**
     * @return array<string, mixed>
     */
    protected function jsonResponse(): array
    {
        $content = $this->client->getResponse()->getContent();

        self::assertIsString($content);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    protected function createUser(
        string $email = 'user@example.com',
        string $username = 'test-user',
        string $plainPassword = 'Password123!',
        ?string $pinHash = null,
        bool $isActive = true,
    ): User {
        $user = new User(
            email: $email,
            username: $username,
            password: '',
        );

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $user->setPin($pinHash);
        $user->setIsActive($isActive);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function assertCookieExists(string $name): Cookie
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                self::assertNotSame('', $cookie->getValue());

                return $cookie;
            }
        }

        self::fail(sprintf('Cookie "%s" was not set.', $name));
    }

    protected function assertCookieMissing(string $name): void
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                self::fail(sprintf('Cookie "%s" was set, but it should not be.', $name));
            }
        }

        self::assertTrue(true);
    }

    protected function assertAuthTokensAreNotExposedInBody(array $responseData): void
    {
        self::assertArrayNotHasKey(CookieFactory::ACCESS_TOKEN_COOKIE, $responseData);
        self::assertArrayNotHasKey(CookieFactory::REFRESH_TOKEN_COOKIE, $responseData);
        self::assertArrayNotHasKey(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE, $responseData);

        $encoded = json_encode($responseData, JSON_THROW_ON_ERROR);

        self::assertStringNotContainsString(CookieFactory::ACCESS_TOKEN_COOKIE, $encoded);
        self::assertStringNotContainsString(CookieFactory::REFRESH_TOKEN_COOKIE, $encoded);
        self::assertStringNotContainsString(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE, $encoded);
    }

    protected function findUserByEmail(string $email): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    /**
     * @return list<Session>
     */
    protected function findSessionsForUser(User $user): array
    {
        /** @var list<Session> $sessions */
        $sessions = $this->entityManager
            ->getRepository(Session::class)
            ->findBy(['user' => $user]);

        return $sessions;
    }

    protected function getResponseCookie(string $name): ?Cookie
    {
        foreach ($this->client->getResponse()->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        return null;
    }

    protected function assertCookieExpired(string $name): Cookie
    {
        $cookie = $this->getResponseCookie($name);

        self::assertInstanceOf(Cookie::class, $cookie, sprintf('Cookie "%s" was not set.', $name));
        self::assertSame('', $cookie->getValue());
        self::assertLessThanOrEqual(time(), $cookie->getExpiresTime());

        return $cookie;
    }

    protected function uniqueEmail(string $prefix = 'user'): string
    {
        return sprintf('%s_%s@example.com', $prefix, bin2hex(random_bytes(6)));
    }

    protected function uniqueUsername(string $prefix = 'user'): string
    {
        return sprintf('%s_%s', $prefix, bin2hex(random_bytes(4)));
    }

    protected function getResponseCookieValue(string $name): string
    {
        $cookie = $this->assertCookieExists($name);

        return $cookie->getValue();
    }

    protected function setBrowserCookie(string $name, string $value): void
    {
        $this->client->getCookieJar()->set(new BrowserKitCookie(
            name: $name,
            value: $value,
            expires: null,
            path: '/',
            domain: 'localhost',
            secure: false,
            httponly: true,
        ));
    }
}