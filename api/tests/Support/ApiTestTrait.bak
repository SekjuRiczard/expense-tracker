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

namespace App\Tests\Support;

use JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use PHPUnit\Framework\Assert;

trait ApiTestTrait
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     * @throws JsonException
     */
    private function postJson(
        KernelBrowser $client,
        string $uri,
        array $payload,
        ?string $token = null,
    ): array {
        $client->request(
            method: 'POST',
            uri: $uri,
            parameters: [],
            files: [],
            server: $this->jsonServerParameters($token),
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        return $this->decodeJsonResponse($client);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function putJson(
        KernelBrowser $client,
        string $uri,
        array $payload,
        ?string $token = null,
    ): array {
        $client->request(
            method: 'PUT',
            uri: $uri,
            parameters: [],
            files: [],
            server: $this->jsonServerParameters($token),
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        return $this->decodeJsonResponse($client);
    }

    /**
     * @return array<string, string>
     */
    private function jsonServerParameters(?string $token = null): array
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
    private function decodeJsonResponse(KernelBrowser $client): array
    {
        $content = $client->getResponse()->getContent();

        Assert::assertIsString($content);
        self::assertJson($content);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    private function uniqueEmail(string $prefix = 'user'): string
    {
        return sprintf('%s_%s@example.com', $prefix, bin2hex(random_bytes(8)));
    }

    /**
     * @return array<string, mixed>
     */
    private function getJson(
        KernelBrowser $client,
        string $uri,
        ?string $token = null,
    ): array {
        $client->request(
            method: 'GET',
            uri: $uri,
            parameters: [],
            files: [],
            server: $this->jsonServerParameters($token),
        );

        return $this->decodeJsonResponse($client);
    }
}