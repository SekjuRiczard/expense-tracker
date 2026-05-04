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
use App\Tests\Support\ApiTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Medium]
final class AuthPinWorkflowTest extends WebTestCase
{
    use ApiTestTrait;

    #[Test]
    public function register_returns_pin_setup_required(): void
    {
        $client = static::createClient();

        $responseData = $this->registerUser($client);

        self::assertResponseStatusCodeSame(201);
        self::assertArrayHasKey('token', $responseData);
        self::assertSame('pin_setup_required', $responseData['status']);
        self::assertSame('User created. PIN setup required.', $responseData['message']);
        self::assertFalse($responseData['user']['hasPin']);
        self::assertArrayHasKey('id', $responseData['user']);
        self::assertArrayHasKey('email', $responseData['user']);
        self::assertArrayHasKey('username', $responseData['user']);
    }

    #[Test]
    public function register_then_setup_pin_returns_authenticated(): void
    {
        $client = static::createClient();

        $registerResponse = $this->registerUser($client);

        $setupResponse = $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        self::assertResponseStatusCodeSame(200);
        self::assertArrayHasKey('token', $setupResponse);
        self::assertSame('authenticated', $setupResponse['status']);
        self::assertSame('PIN successfully set up.', $setupResponse['message']);
        self::assertTrue($setupResponse['user']['hasPin']);
    }

    #[Test]
    public function login_user_with_pin_returns_pin_verification_required(): void
    {
        $client = static::createClient();

        $email = $this->uniqueEmail('login_with_pin');
        $password = 'password123';

        $registerResponse = $this->registerUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $loginResponse = $this->loginUser(
            client: $client,
            email: $email,
            password: $password,
        );

        self::assertResponseStatusCodeSame(200);
        self::assertArrayHasKey('token', $loginResponse);
        self::assertSame('pin_verification_required', $loginResponse['status']);
        self::assertSame('Password verified. PIN verification required.', $loginResponse['message']);
        self::assertTrue($loginResponse['user']['hasPin']);
    }

    #[Test]
    public function login_then_verify_pin_returns_authenticated(): void
    {
        $client = static::createClient();

        $email = $this->uniqueEmail('verify_pin');
        $password = 'password123';

        $registerResponse = $this->registerUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $loginResponse = $this->loginUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $verifyResponse = $this->verifyPin(
            client: $client,
            partialToken: (string) $loginResponse['token'],
        );

        self::assertResponseStatusCodeSame(200);
        self::assertArrayHasKey('token', $verifyResponse);
        self::assertSame('authenticated', $verifyResponse['status']);
        self::assertSame('PIN verified successfully.', $verifyResponse['message']);
        self::assertTrue($verifyResponse['user']['hasPin']);
    }

    #[Test]
    public function login_user_without_pin_returns_pin_setup_required(): void
    {
        $client = static::createClient();

        $email = $this->uniqueEmail('login_without_pin');
        $password = 'password123';

        $this->registerUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $loginResponse = $this->loginUser(
            client: $client,
            email: $email,
            password: $password,
        );

        self::assertResponseStatusCodeSame(200);
        self::assertArrayHasKey('token', $loginResponse);
        self::assertSame('pin_setup_required', $loginResponse['status']);
        self::assertSame('Password verified. PIN setup required.', $loginResponse['message']);
        self::assertFalse($loginResponse['user']['hasPin']);
    }

    #[Test]
    public function partial_token_cannot_access_pin_change(): void
    {
        $client = static::createClient();

        $email = $this->uniqueEmail('partial_block');
        $password = 'password123';

        $registerResponse = $this->registerUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $loginResponse = $this->loginUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $responseData = $this->putJson(
            client: $client,
            uri: '/api/pin/change',
            payload: [
                'oldPin' => '123456',
                'newPin' => '654321',
            ],
            token: (string) $loginResponse['token'],
        );

        self::assertResponseStatusCodeSame(403);
        self::assertSame('pin_verification_required', $responseData['status']);
        self::assertSame('PIN authorization is required to access this resource.', $responseData['message']);
    }

    #[Test]
    public function full_token_can_change_pin(): void
    {
        $client = static::createClient();

        $registerResponse = $this->registerUser($client);

        $setupResponse = $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $responseData = $this->putJson(
            client: $client,
            uri: '/api/pin/change',
            payload: [
                'oldPin' => '123456',
                'newPin' => '654321',
            ],
            token: (string) $setupResponse['token'],
        );

        self::assertResponseStatusCodeSame(200);
        self::assertSame('success', $responseData['status']);
        self::assertSame('PIN successfully changed.', $responseData['message']);
    }

    #[Test]
    public function old_partial_token_does_not_work_after_setup_pin(): void
    {
        $client = static::createClient();

        $registerResponse = $this->registerUser($client);
        $partialToken = (string) $registerResponse['token'];

        $this->setupPin(
            client: $client,
            partialToken: $partialToken,
        );

        $responseData = $this->postJson(
            client: $client,
            uri: '/api/pin/setup',
            payload: [
                'pin' => '123456',
            ],
            token: $partialToken,
        );

        self::assertResponseStatusCodeSame(401);
        self::assertSame('unauthorized', $responseData['status']);
        self::assertSame('Invalid or expired session.', $responseData['message']);
    }

    #[Test]
    public function old_partial_token_does_not_work_after_verify_pin(): void
    {
        $client = static::createClient();

        $email = $this->uniqueEmail('old_partial_after_verify');
        $password = 'password123';

        $registerResponse = $this->registerUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $loginResponse = $this->loginUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $partialToken = (string) $loginResponse['token'];

        $this->verifyPin(
            client: $client,
            partialToken: $partialToken,
        );

        $responseData = $this->postJson(
            client: $client,
            uri: '/api/pin/verify',
            payload: [
                'pin' => '123456',
            ],
            token: $partialToken,
        );

        self::assertResponseStatusCodeSame(401);
        self::assertSame('unauthorized', $responseData['status']);
        self::assertSame('Invalid or expired session.', $responseData['message']);
    }

    #[Test]
    public function wrong_pin_returns_403(): void
    {
        $client = static::createClient();

        $email = $this->uniqueEmail('wrong_pin');
        $password = 'password123';

        $registerResponse = $this->registerUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $loginResponse = $this->loginUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $responseData = $this->postJson(
            client: $client,
            uri: '/api/pin/verify',
            payload: [
                'pin' => '000000',
            ],
            token: (string) $loginResponse['token'],
        );

        self::assertResponseStatusCodeSame(403);
        self::assertSame('error', $responseData['status']);
        self::assertSame('Invalid PIN.', $responseData['message']);
    }

    #[Test]
    public function three_wrong_pins_lock_user(): void
    {
        $client = static::createClient();

        $email = $this->uniqueEmail('pin_lock');
        $password = 'password123';

        $registerResponse = $this->registerUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $loginResponse = $this->loginUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $partialToken = (string) $loginResponse['token'];

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->postJson(
                client: $client,
                uri: '/api/pin/verify',
                payload: [
                    'pin' => '000000',
                ],
                token: $partialToken,
            );

            self::assertResponseStatusCodeSame(403);
        }

        $this->postJson(
            client: $client,
            uri: '/api/pin/verify',
            payload: [
                'pin' => '123456',
            ],
            token: $partialToken,
        );

        self::assertResponseStatusCodeSame(403);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        /** @var User|null $user */
        $user = $entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        self::assertNotNull($user);
        self::assertNotNull($user->getPinLockedUntil());
    }

    /**
     * @return array<string, mixed>
     */
    private function registerUser(
        KernelBrowser $client,
        ?string $email = null,
        string $password = 'password123',
    ): array {
        return $this->postJson(
            client: $client,
            uri: '/api/register',
            payload: [
                'username' => 'john',
                'email' => $email ?? $this->uniqueEmail('register'),
                'password' => $password,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function loginUser(
        KernelBrowser $client,
        string $email,
        string $password = 'password123',
    ): array {
        return $this->postJson(
            client: $client,
            uri: '/api/login',
            payload: [
                'email' => $email,
                'password' => $password,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function setupPin(
        KernelBrowser $client,
        string $partialToken,
        string $pin = '123456',
    ): array {
        return $this->postJson(
            client: $client,
            uri: '/api/pin/setup',
            payload: [
                'pin' => $pin,
            ],
            token: $partialToken,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function verifyPin(
        KernelBrowser $client,
        string $partialToken,
        string $pin = '123456',
    ): array {
        return $this->postJson(
            client: $client,
            uri: '/api/pin/verify',
            payload: [
                'pin' => $pin,
            ],
            token: $partialToken,
        );
    }

    #[Test]
    public function logout_revokes_current_session(): void
    {
        $client = static::createClient();

        $registerResponse = $this->registerUser($client);

        $setupResponse = $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $fullToken = (string) $setupResponse['token'];

        $logoutResponse = $this->postJson(
            client: $client,
            uri: '/api/logout',
            payload: [],
            token: $fullToken,
        );

        self::assertResponseStatusCodeSame(200);
        self::assertSame('success', $logoutResponse['status']);
        self::assertSame('Logged out successfully.', $logoutResponse['message']);

        $responseData = $this->putJson(
            client: $client,
            uri: '/api/pin/change',
            payload: [
                'oldPin' => '123456',
                'newPin' => '654321',
            ],
            token: $fullToken,
        );

        self::assertResponseStatusCodeSame(401);
        self::assertSame('unauthorized', $responseData['status']);
        self::assertSame('Invalid or expired session.', $responseData['message']);
    }

    #[Test]
    public function me_returns_pin_setup_required_for_partial_setup_session(): void
    {
        $client = static::createClient();

        $registerResponse = $this->registerUser($client);

        $responseData = $this->getJson(
            client: $client,
            uri: '/api/me',
            token: (string) $registerResponse['token'],
        );

        self::assertResponseStatusCodeSame(200);
        self::assertSame('pin_setup_required', $responseData['status']);
        self::assertFalse($responseData['user']['hasPin']);
        self::assertArrayHasKey('email', $responseData['user']);
        self::assertArrayHasKey('username', $responseData['user']);
    }

    #[Test]
    public function me_returns_authenticated_for_full_session(): void
    {
        $client = static::createClient();

        $registerResponse = $this->registerUser($client);

        $setupResponse = $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $responseData = $this->getJson(
            client: $client,
            uri: '/api/me',
            token: (string) $setupResponse['token'],
        );

        self::assertResponseStatusCodeSame(200);
        self::assertSame('authenticated', $responseData['status']);
        self::assertTrue($responseData['user']['hasPin']);
    }

    #[Test]
    public function me_returns_pin_verification_required_for_login_partial_session(): void
    {
        $client = static::createClient();

        $email = $this->uniqueEmail('me_verify');
        $password = 'password123';

        $registerResponse = $this->registerUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $loginResponse = $this->loginUser(
            client: $client,
            email: $email,
            password: $password,
        );

        $responseData = $this->getJson(
            client: $client,
            uri: '/api/me',
            token: (string) $loginResponse['token'],
        );

        self::assertResponseStatusCodeSame(200);
        self::assertSame('pin_verification_required', $responseData['status']);
        self::assertTrue($responseData['user']['hasPin']);
    }

    #[Test]
    public function me_returns_401_without_token(): void
    {
        $client = static::createClient();

        $responseData = $this->getJson(
            client: $client,
            uri: '/api/me',
        );

        self::assertResponseStatusCodeSame(401);
        self::assertArrayHasKey('message', $responseData);
    }

    #[Test]
    public function login_is_rate_limited_after_too_many_attempts(): void
    {
        $client = static::createClient();

        $email = $this->uniqueEmail('rate_limit');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $responseData = $this->loginUser(
                client: $client,
                email: $email,
                password: 'wrong-password',
            );

            self::assertResponseStatusCodeSame(401);
            self::assertSame('error', $responseData['status']);
            self::assertSame('Invalid email or password.', $responseData['message']);
        }

        $responseData = $this->loginUser(
            client: $client,
            email: $email,
            password: 'wrong-password',
        );

        self::assertResponseStatusCodeSame(429);
        self::assertSame('error', $responseData['status']);
        self::assertSame('Too many login attempts. Try again later.', $responseData['message']);
    }

    #[Test]
    public function setup_pin_returns_refresh_token_for_authenticated_session(): void
    {
        $client = static::createClient();

        $registerResponse = $this->registerUser($client);

        $setupResponse = $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        self::assertResponseStatusCodeSame(200);
        self::assertSame('authenticated', $setupResponse['status']);
        self::assertArrayHasKey('token', $setupResponse);
        self::assertArrayHasKey('refreshToken', $setupResponse);
    }

    #[Test]
    public function refresh_token_returns_new_access_and_refresh_tokens(): void
    {
        $client = static::createClient();

        $registerResponse = $this->registerUser($client);

        $setupResponse = $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $refreshResponse = $this->postJson(
            client: $client,
            uri: '/api/token/refresh',
            payload: [
                'refreshToken' => (string) $setupResponse['refreshToken'],
            ],
        );

        self::assertResponseStatusCodeSame(200);
        self::assertSame('authenticated', $refreshResponse['status']);
        self::assertSame('Token refreshed successfully.', $refreshResponse['message']);
        self::assertArrayHasKey('token', $refreshResponse);
        self::assertArrayHasKey('refreshToken', $refreshResponse);
        self::assertNotSame($setupResponse['token'], $refreshResponse['token']);
        self::assertNotSame($setupResponse['refreshToken'], $refreshResponse['refreshToken']);
    }

    #[Test]
    public function old_refresh_token_is_invalid_after_rotation(): void
    {
        $client = static::createClient();

        $registerResponse = $this->registerUser($client);

        $setupResponse = $this->setupPin(
            client: $client,
            partialToken: (string) $registerResponse['token'],
        );

        $oldRefreshToken = (string) $setupResponse['refreshToken'];

        $this->postJson(
            client: $client,
            uri: '/api/token/refresh',
            payload: [
                'refreshToken' => $oldRefreshToken,
            ],
        );

        self::assertResponseStatusCodeSame(200);

        $responseData = $this->postJson(
            client: $client,
            uri: '/api/token/refresh',
            payload: [
                'refreshToken' => $oldRefreshToken,
            ],
        );

        self::assertResponseStatusCodeSame(401);
        self::assertSame('error', $responseData['status']);
        self::assertSame('Invalid or expired refresh token.', $responseData['message']);
    }

    #[Test]
    public function invalid_refresh_token_returns_401(): void
    {
        $client = static::createClient();

        $responseData = $this->postJson(
            client: $client,
            uri: '/api/token/refresh',
            payload: [
                'refreshToken' => 'invalid-refresh-token',
            ],
        );

        self::assertResponseStatusCodeSame(401);
        self::assertSame('error', $responseData['status']);
        self::assertSame('Invalid or expired refresh token.', $responseData['message']);
    }
}

