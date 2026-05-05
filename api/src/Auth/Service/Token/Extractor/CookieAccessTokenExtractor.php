<?php

declare(strict_types=1);

namespace App\Auth\Service\Token\Extractor;

use Symfony\Component\HttpFoundation\Request;

final class CookieAccessTokenExtractor extends AbstractRequestTokenExtractor
{
    public function __construct(
        private readonly string $cookieName = 'access_token',
    ) {
    }

    public function extract(Request $request): string
    {
        $token = $request->cookies->get($this->cookieName);

        return $this->normalizeToken(
            token: is_string($token) ? $token : null,
            missingMessage: sprintf('Missing "%s" cookie.', $this->cookieName),
        );
    }
}
