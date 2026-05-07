<?php

declare(strict_types=1);

namespace App\Auth\EventSubscriber;

use App\Auth\Factory\CookieFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class AuthCookieSubscriber implements EventSubscriberInterface
{
    public function __construct(private CookieFactory $cookieFactory)
    {}
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }
    public function onKernelResponse(ResponseEvent $event): void
    {
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $event->getRequest();
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $event->getResponse();
        /** @var \Symfony\Component\HttpFoundation\ParameterBag $attr */
        $attr = $request->attributes;
        if ($attr->has('_logout')) {
            $response->headers->setCookie($this->cookieFactory->expireCookie(CookieFactory::ACCESS_TOKEN_COOKIE));
            $response->headers->setCookie($this->cookieFactory->expireCookie(CookieFactory::REFRESH_TOKEN_COOKIE));
            $response->headers->setCookie($this->cookieFactory->expireCookie(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE));
            return;
        }

        $tokenMap = [
            '_partial_auth_token' => [CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE, 900],
            '_auth_token' => [CookieFactory::ACCESS_TOKEN_COOKIE, 900],
            '_refresh_token' => [CookieFactory::REFRESH_TOKEN_COOKIE, 604800],
        ];
        /** @var string $key */
        /** @var array $config */
        foreach ($tokenMap as $key => $config) {
            if ($attr->has($key)) {
                $response->headers->setCookie($this->cookieFactory->createCookie($config[0], (string) $attr->get($key), $config[1]));
            }
        }

        if ($attr->has('_expire_partial')) {
            $response->headers->setCookie($this->cookieFactory->expireCookie(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE));
        }

        return;
    }
}