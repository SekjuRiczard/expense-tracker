<?php

namespace App\Auth\Service\Token\Transport;

use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class JsonBodyTokenTransport extends AbstractTokenTransport
{
    /**
     * @param array<string, mixed> $payload
     */
    protected function applyPayload(JsonResponse $response, array $payload): JsonResponse
    {
        $currentPayload = json_decode((string) $response->getContent(), true);

        if (!is_array($currentPayload)) {
            $currentPayload = [];
        }

        $response->setData([
            ...$payload,
            ...$currentPayload,
        ]);

        return $response;
    }
}