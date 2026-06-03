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

namespace App\Session\EventSubscriber;

use App\Auth\Security\CookieTokenExtractor;
use App\Entity\Session;
use App\Enum\SessionStatus;
use App\Session\Service\SessionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class SessionAuthorizationSubscriber implements EventSubscriberInterface
{
    private const PUBLIC_PATHS = [
        '/api/register',
        '/api/login',
        '/api/token/refresh',
        '/api/doc',
        '/api/password/forgot',
        '/api/password/reset',
    ];

    private const PARTIAL_AUTH_ALLOWED_PATHS = [
        '/api/pin/setup',
        '/api/pin/verify',
        '/api/logout',
        '/api/me',
    ];

    public function __construct(
        private SessionManagerInterface $sessionManager,
        private CookieTokenExtractor $tokenExtractor,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        /** @var Request $request */
        $request = $event->getRequest();
        if (!$this->isApiRequest($request) || $this->isPublicPath($request)) {
            return;
        }
        /** @var Session $session */
        $session = $this->getSessionFromRequest($request);
        if (!$session instanceof Session) {
            $this->deny($event, Response::HTTP_UNAUTHORIZED, 'unauthorized', 'Invalid or expired session.');

            return;
        }
        $request->attributes->set('app_session', $session);
        if (SessionStatus::AUTHENTICATED === $session->getStatus() || $this->isPartialAuthAllowedPath($request)) {
            return;
        }
        $this->deny($event, Response::HTTP_FORBIDDEN, $session->getStatus()->value, 'PIN authorization is required.');
    }

    private function getSessionFromRequest(Request $request): ?Session
    {
        /** @var string|false $token */
        $token = $this->tokenExtractor->extract($request);
        if (!is_string($token) || '' === $token) {
            return null;
        }

        return $this->sessionManager->findSessionByToken($token);
    }

    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($this->normalizePath($request), '/api');
    }

    private function isPublicPath(Request $request): bool
    {
        return in_array($this->normalizePath($request), self::PUBLIC_PATHS, true);
    }

    private function isPartialAuthAllowedPath(Request $request): bool
    {
        return in_array($this->normalizePath($request), self::PARTIAL_AUTH_ALLOWED_PATHS, true);
    }

    private function normalizePath(Request $request): string
    {
        $path = $request->getPathInfo();

        return '/' === $path ? $path : rtrim($path, '/');
    }

    private function deny(ControllerEvent $event, int $statusCode, string $status, string $message): void
    {
        $event->setController(
            static fn (): JsonResponse => new JsonResponse([
                'status' => $status,
                'message' => $message,
            ], $statusCode)
        );
    }
}
