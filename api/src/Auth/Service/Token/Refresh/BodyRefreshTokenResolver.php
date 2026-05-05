<?php

declare(strict_types=1);

namespace App\Service\Token\Refresh;

use Symfony\Component\HttpFoundation\Request;

final class BodyRefreshTokenResolver extends AbstractRefreshTokenResolver
{
    protected function doResolve(Request $request): ?string
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return null;
        }

        $refreshToken = $payload['refreshToken'] ?? null;

        return is_string($refreshToken) ? $refreshToken : null;
    }
}
