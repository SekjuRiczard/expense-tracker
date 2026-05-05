<?php

declare(strict_types=1);

namespace App\Service\Token\Transport;

use App\Dto\AuthTokenResponse;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract readonly class AbstractTokenTransport implements TokenTransportInterface
{
    final public function apply(JsonResponse $response, AuthTokenResponse $tokenResponse): JsonResponse
    {
        $payload = $tokenResponse->toArray();
        $this->validatePayload($payload);

        return $this->applyPayload($response, $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    abstract protected function applyPayload(JsonResponse $response, array $payload): JsonResponse;

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePayload(array $payload): void
    {
        $token = $payload['token'] ?? null;

        if (!is_string($token) || trim($token) === '') {
            throw new InvalidArgumentException('Access token cannot be empty.');
        }
    }
}
