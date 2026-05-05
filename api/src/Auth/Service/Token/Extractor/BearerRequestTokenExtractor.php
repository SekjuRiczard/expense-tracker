<?php

declare(strict_types=1);

namespace App\Auth\Service\Token\Extractor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class BearerRequestTokenExtractor extends AbstractRequestTokenExtractor
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

        return $this->normalizeToken(
            token: substr($authorizationHeader, 7),
            missingMessage: 'Missing bearer token.',
        );
    }
}
