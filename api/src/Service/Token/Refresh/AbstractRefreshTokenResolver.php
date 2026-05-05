<?php

declare(strict_types=1);

namespace App\Service\Token\Refresh;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractRefreshTokenResolver implements RefreshTokenResolverInterface
{
    final public function resolve(Request $request): ?string
    {
        $token = $this->doResolve($request);

        if (!is_string($token)) {
            return null;
        }

        $token = trim($token);

        return $token !== '' ? $token : null;
    }

    abstract protected function doResolve(Request $request): ?string;
}
