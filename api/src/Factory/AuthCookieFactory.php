<?php

declare(strict_types=1);

namespace App\Factory;

use Symfony\Component\HttpFoundation\Cookie;

final readonly class AuthCookieFactory
{
    public function __construct(
        private string $accessCookieName = 'access_token',
        private string $refreshCookieName = 'refresh_token',
        private string $accessCookiePath = '/',
        private string $refreshCookiePath = '/api/token',
        private string $sameSite = Cookie::SAMESITE_LAX,
        private bool $secure = true,
    ) {
    }

    public function createAccessTokenCookie(string $token): Cookie
    {
        return Cookie::create($this->accessCookieName)
            ->withValue($token)
            ->withPath($this->accessCookiePath)
            ->withSecure($this->secure)
            ->withHttpOnly(true)
            ->withSameSite($this->sameSite);
    }

    public function createRefreshTokenCookie(string $refreshToken): Cookie
    {
        return Cookie::create($this->refreshCookieName)
            ->withValue($refreshToken)
            ->withPath($this->refreshCookiePath)
            ->withSecure($this->secure)
            ->withHttpOnly(true)
            ->withSameSite($this->sameSite);
    }

    public function createExpiredAccessTokenCookie(): Cookie
    {
        return Cookie::create($this->accessCookieName)
            ->withValue('')
            ->withExpires(1)
            ->withPath($this->accessCookiePath)
            ->withSecure($this->secure)
            ->withHttpOnly(true)
            ->withSameSite($this->sameSite);
    }

    public function createExpiredRefreshTokenCookie(): Cookie
    {
        return Cookie::create($this->refreshCookieName)
            ->withValue('')
            ->withExpires(1)
            ->withPath($this->refreshCookiePath)
            ->withSecure($this->secure)
            ->withHttpOnly(true)
            ->withSameSite($this->sameSite);
    }
}
