<?php

/*
 * This file is part of the Expense Tracker.
 *
 * (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Session;
use App\Enum\SessionStatus;
use App\Service\BearerTokenExtractor;
use App\Service\SessionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class SessionAuthorizationSubscriber implements EventSubscriberInterface
{
    private const PUBLIC_PATHS = [
        '/api/register',
        '/api/login',
        '/api/token/refresh',
        '/api/doc',
    ];

    private const PARTIAL_AUTH_ALLOWED_PATHS = [
        '/api/pin/setup',
        '/api/pin/verify',
        '/api/logout',
        '/api/me',
    ];

    public function __construct(
        private BearerTokenExtractor $bearerTokenExtractor,
        private SessionManagerInterface $sessionManager,
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
        $request = $event->getRequest();

        if (!$this->isApiRequest($request)) {
            return;
        }

        if ($this->isPublicPath($request)) {
            return;
        }

        $session = $this->getSessionFromRequest($request);

        if (!$session instanceof Session) {
            $this->deny(
                event: $event,
                statusCode: Response::HTTP_UNAUTHORIZED,
                status: 'unauthorized',
                message: 'Invalid or expired session.',
            );

            return;
        }

        if ($session->getStatus() === SessionStatus::AUTHENTICATED) {
            return;
        }

        if ($this->isPartialAuthAllowedPath($request)) {
            return;
        }

        $this->deny(
            event: $event,
            statusCode: Response::HTTP_FORBIDDEN,
            status: $session->getStatus()->value,
            message: 'PIN authorization is required to access this resource.',
        );
    }

    private function getSessionFromRequest(Request $request): ?Session
    {
        try {
            $token = $this->bearerTokenExtractor->extract($request);
        } catch (UnauthorizedHttpException) {
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

        if ($path === '/') {
            return $path;
        }

        return rtrim($path, '/');
    }

    private function deny(
        ControllerEvent $event,
        int $statusCode,
        string $status,
        string $message,
    ): void {
        $event->setController(static fn (): JsonResponse => new JsonResponse([
            'status' => $status,
            'message' => $message,
        ], $statusCode));
    }
}