<?php

declare(strict_types=1);

namespace App\Auth\Security;

use App\Auth\Factory\CookieFactory;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

final class CookieTokenExtractor implements TokenExtractorInterface
{
    public function extract(Request $request): string|false
    {
        $accessToken = $request->cookies->get(CookieFactory::ACCESS_TOKEN_COOKIE);
        $partialAccessToken = $request->cookies->get(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);

        file_put_contents(
            dirname(__DIR__, 3) . '/var/log/cookie_token_extractor.log',
            sprintf(
                "[%s] path=%s access=%s partial=%s returning=%s\n",
                (new \DateTimeImmutable())->format('c'),
                $request->getPathInfo(),
                is_string($accessToken) && $accessToken !== '' ? 'yes' : 'no',
                is_string($partialAccessToken) && $partialAccessToken !== '' ? 'yes' : 'no',
                is_string($accessToken) && $accessToken !== ''
                    ? 'access_token'
                    : (
                is_string($partialAccessToken) && $partialAccessToken !== ''
                    ? 'partial_access_token'
                    : 'false'
                )
                ,
            ),
            FILE_APPEND
        );

        if (is_string($accessToken) && $accessToken !== '') {
            return $accessToken;
        }

        if (is_string($partialAccessToken) && $partialAccessToken !== '') {
            return $partialAccessToken;
        }

        return false;
    }
}