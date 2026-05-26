<?php

/**
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

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
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        /** @var Request $request */
        $request = $event->getRequest();
        /** @var Response $response */
        $response = $event->getResponse();
        if ($request->attributes->has('_logout')) {
            /** @var string $cookieName */
            foreach ([
                         CookieFactory::ACCESS_TOKEN_COOKIE,
                         CookieFactory::REFRESH_TOKEN_COOKIE,
                         CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE,
                     ] as $cookieName) {
                $response->headers->setCookie($this->cookieFactory->expireCookie($cookieName));
            }

            return;
        }
        /** @var array<string, array{0: string, 1: int}> $tokenMap */
        $tokenMap = [
            '_partial_auth_token' => [CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE, 900],
            '_auth_token' => [CookieFactory::ACCESS_TOKEN_COOKIE, 900],
            '_refresh_token' => [CookieFactory::REFRESH_TOKEN_COOKIE, 604800],
        ];
        /** @var string $attributeName */
        /** @var array{0: string, 1: int} $cookieConfig */
        foreach ($tokenMap as $attributeName => $cookieConfig) {
            if (!$request->attributes->has($attributeName)) {
                continue;
            }
            $response->headers->setCookie(
                $this->cookieFactory->createCookie(
                    $cookieConfig[0],
                    (string) $request->attributes->get($attributeName),
                    $cookieConfig[1],
                ),
            );
        }
        if ($request->attributes->has('_expire_partial')) {
            $response->headers->setCookie(
                $this->cookieFactory->expireCookie(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE),
            );
        }
    }
}
