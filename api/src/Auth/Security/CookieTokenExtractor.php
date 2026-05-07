<?php

declare(strict_types=1);

namespace App\Auth\Security;

use App\Auth\Factory\CookieFactory;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

final class CookieTokenExtractor implements TokenExtractorInterface
{
    public function extract(Request $request): ?string
    {
        /** @var string|null $token */
        $token = $request->cookies->get(CookieFactory::ACCESS_TOKEN_COOKIE)
            ?? $request->cookies->get(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);

        return $token;
    }
}