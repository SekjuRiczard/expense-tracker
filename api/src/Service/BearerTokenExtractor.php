<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class BearerTokenExtractor
{
    public function extract(Request $request): string
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if ($authorizationHeader === null) {
            throw new UnauthorizedHttpException('Bearer', 'Missing Authorization header.');
        }

        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid Authorization header.');
        }

        $token = trim(substr($authorizationHeader, 7));

        if ($token === '') {
            throw new UnauthorizedHttpException('Bearer', 'Missing bearer token.');
        }

        return $token;
    }
}