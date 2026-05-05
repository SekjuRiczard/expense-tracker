<?php

declare(strict_types=1);

namespace App\Tests\Support;

use JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiTestCase extends WebTestCase
{
    protected const DEFAULT_PASSWORD = 'password123';
    protected const DEFAULT_PIN = '123456';

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    protected function postJson(string $uri, array $payload = [], ?string $token = null): array
    {
        return $this->requestJson('POST', $uri, $payload, $token);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    protected function putJson(string $uri, array $payload = [], ?string $token = null): array
    {
        return $this->requestJson('PUT', $uri, $payload, $token);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getJson(string $uri, ?string $token = null): array
    {
        return $this->requestJson('GET', $uri, [], $token);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    protected function requestJson(string $method, string $uri, array $payload = [], ?string $token = null): array
    {
        $this->client->request(
            method: $method,
            uri: $uri,
            parameters: [],
            files: [],
            server: $this->jsonServerParameters($token),
            content: $method === 'GET' ? null : $this->encodeJson($payload),
        );

        return $this->jsonResponseData();
    }

    /**
     * @return array<string, string>
     */
    protected function jsonServerParameters(?string $token = null): array
    {
        $parameters = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        if ($token !== null) {
            $parameters['HTTP_AUTHORIZATION'] = sprintf('Bearer %s', $token);
        }

        return $parameters;
    }

    /**
     * @return array<string, mixed>
     */
    protected function jsonResponseData(): array
    {
        $content = $this->client->getResponse()->getContent();

        self::assertIsString($content);
        self::assertJson($content);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encodeJson(array $payload): string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            self::fail(sprintf('Cannot encode request payload to JSON: %s', $exception->getMessage()));
        }
    }

    protected function assertHttpStatus(int $expectedStatusCode): void
    {
        self::assertResponseStatusCodeSame($expectedStatusCode);
    }

    /**
     * @param array<string, mixed> $responseData
     */
    protected function assertErrorResponse(
        array $responseData,
        int $statusCode,
        string $status,
        string $message,
    ): void {
        $this->assertHttpStatus($statusCode);
        self::assertSame($status, $responseData['status'] ?? null);
        self::assertSame($message, $responseData['message'] ?? null);
    }

    /**
     * @param array<string, mixed> $responseData
     */
    protected function assertTokenPayload(array $responseData, bool $refreshTokenExpected): void
    {
        self::assertArrayHasKey('token', $responseData);
        self::assertIsString($responseData['token']);
        self::assertNotSame('', $responseData['token']);

        if ($refreshTokenExpected) {
            self::assertArrayHasKey('refreshToken', $responseData);
            self::assertIsString($responseData['refreshToken']);
            self::assertNotSame('', $responseData['refreshToken']);

            return;
        }

        self::assertArrayNotHasKey('refreshToken', $responseData);
    }

    /**
     * @param array<string, mixed> $responseData
     */
    protected function assertUserPayload(array $responseData, bool $hasPin): void
    {
        self::assertArrayHasKey('user', $responseData);
        self::assertIsArray($responseData['user']);

        /** @var array<string, mixed> $user */
        $user = $responseData['user'];

        self::assertArrayHasKey('id', $user);
        self::assertArrayHasKey('email', $user);
        self::assertArrayHasKey('username', $user);
        self::assertArrayHasKey('hasPin', $user);
        self::assertIsString($user['id']);
        self::assertIsString($user['email']);
        self::assertIsString($user['username']);
        self::assertSame($hasPin, $user['hasPin']);
    }

    protected function uniqueEmail(string $prefix = 'user'): string
    {
        return sprintf('%s_%s@example.com', $prefix, bin2hex(random_bytes(8)));
    }

    protected function assertUnauthorizedInvalidSession(array $responseData): void
    {
        $this->assertErrorResponse(
            responseData: $responseData,
            statusCode: Response::HTTP_UNAUTHORIZED,
            status: 'unauthorized',
            message: 'Invalid or expired session.',
        );
    }
}
