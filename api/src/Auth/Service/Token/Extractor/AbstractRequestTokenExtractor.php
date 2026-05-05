<?php

declare(strict_types=1);

namespace App\Auth\Service\Token\Extractor;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

abstract class AbstractRequestTokenExtractor implements RequestTokenExtractorInterface
{
    protected function normalizeToken(?string $token, string $missingMessage): string
    {
        if ($token === null) {
            throw new UnauthorizedHttpException('Bearer', $missingMessage);
        }

        $token = trim($token);

        if ($token === '') {
            throw new UnauthorizedHttpException('Bearer', $missingMessage);
        }

        return $token;
    }
}
