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

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
final class AuthenticationTest extends WebTestCase
{
    private const URI_REGISTER = '/api/register';
    private const URI_LOGIN = '/api/login_check';
    private const URI_REFRESH_TOKEN = '/api/token/refresh';
    private const JSON_HEADERS = ['CONTENT_TYPE' => 'application/json'];

    public function test_it_returns_jwt_token_on_successful_login(): void
    {
        /** @var KernelBrowser $client */
        $client = static::createClient();
        /** @var String $password */
        $password = 'SuperSecret123!';
        /** @var String $email */
        $email = 'jwt_test@example.com';
        $this->postJson($client, self::URI_REGISTER, [
            'username' => 'jwt_tester',
            'email' => $email,
            'password' => $password
        ], 201);
        /** @var array<string, mixed> $responseData */
        $responseData = $this->postJson($client, self::URI_LOGIN, [
            'email' => $email,
            'password' => $password
        ]);
        $this->assertArrayHasKey('token', $responseData, 'Response should contain a JWT token.');
    }

    public function test_it_fails_on_invalid_credentials(): void
    {
        /** @var KernelBrowser $client */
        $client = static::createClient();
        /** @var array<string, mixed> $responseData */
        $responseData = $this->postJson($client, self::URI_LOGIN, [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ], 401);

        $this->assertArrayHasKey('code', $responseData);
        $this->assertEquals(401, $responseData['code']);
        $this->assertEquals('Invalid credentials.', $responseData['message']);
    }

    public function test_it_can_refresh_jwt_token(): void
    {
        /** @var KernelBrowser $client */
        $client = static::createClient();
        /** @var string $email */
        $email = 'refresh_test@example.com';
        /** @var string $password */
        $password = 'SuperSecret123!';
        $this->postJson($client, self::URI_REGISTER, [
            'username' => 'refresh_tester',
            'email' => $email,
            'password' => $password
        ], 201);
        $data = $this->postJson($client, self::URI_LOGIN, [
            'email' => $email,
            'password' => $password
        ]);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
        /** @var string $refreshToken */
        $refreshToken = $data['refresh_token'];
        $newData = $this->postJson($client, self::URI_REFRESH_TOKEN, [
            'refresh_token' => $refreshToken
        ]);
        $this->assertArrayHasKey('token', $newData);
        $this->assertArrayHasKey('refresh_token', $newData);
    }

    /**
     * @param KernelBrowser $client
     * @param string $uri
     * @param array<string, mixed> $payload
     * @param int $expectedStatusCode
     * @return array<string, mixed>
     */
    private function postJson(KernelBrowser $client, string $uri, array $payload, int $expectedStatusCode = 200): array
    {
        $client->request(
            'POST',
            $uri,
            [],
            [],
            self::JSON_HEADERS,
            (string) json_encode($payload)
        );
        $this->assertResponseStatusCodeSame($expectedStatusCode);
        /** @var String $responseContent */
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        /** @var array<string, mixed> $decodedData */
        $decodedData = json_decode($responseContent, true);

        return $decodedData;
    }
}