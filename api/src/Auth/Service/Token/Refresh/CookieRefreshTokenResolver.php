<?php

declare(strict_types=1);

namespace App\Auth\Service\Token\Refresh;

use Symfony\Component\HttpFoundation\Request;

final class CookieRefreshTokenResolver extends AbstractRefreshTokenResolver
{
    public function __construct(
        private readonly string $cookieName = 'refresh_token',
    ) {
    }

    protected function doResolve(Request $request): ?string
    {
        $refreshToken = $request->cookies->get($this->cookieName);

        return is_string($refreshToken) ? $refreshToken : null;
    }
}
