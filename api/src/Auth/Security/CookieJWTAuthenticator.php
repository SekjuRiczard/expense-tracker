<?php

declare(strict_types=1);

namespace App\Auth\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;

final class CookieJWTAuthenticator extends JWTAuthenticator
{
    protected function getTokenExtractor(): TokenExtractorInterface
    {
        return new CookieTokenExtractor();
    }
}