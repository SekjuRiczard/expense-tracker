<?php

declare(strict_types=1);

namespace App\Auth\Service\Token\Transport;

use App\Auth\Factory\AuthCookieFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class CookieTokenTransport extends AbstractTokenTransport
{
    public function __construct(
        private AuthCookieFactory $cookieFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function applyPayload(JsonResponse $response, array $payload): JsonResponse
    {
        /** @var string $accessToken */
        $accessToken = $payload['token'];
        $response->headers->setCookie($this->cookieFactory->createAccessTokenCookie($accessToken));

        $refreshToken = $payload['refreshToken'] ?? null;

        if (is_string($refreshToken) && $refreshToken !== '') {
            $response->headers->setCookie($this->cookieFactory->createRefreshTokenCookie($refreshToken));
        }

        return $response;
    }
}