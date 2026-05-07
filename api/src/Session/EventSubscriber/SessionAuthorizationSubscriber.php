<?php

declare(strict_types=1);

namespace App\Session\EventSubscriber;

use App\Auth\Factory\CookieFactory;
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
    private const PUBLIC_PATHS = ['/api/register', '/api/login', '/api/token/refresh', '/api/doc'];
    private const PARTIAL_AUTH_ALLOWED_PATHS = ['/api/pin/setup', '/api/pin/verify', '/api/logout', '/api/me'];

    public function __construct(private SessionManagerInterface $sessionManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onKernelController'];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$this->isApiRequest($event->getRequest()) || $this->isPublicPath($event->getRequest())) {
            return;
        }

        /** @var Session|null $session */
        $session = $this->getSessionFromRequest($event->getRequest());

        if (!$session instanceof Session) {
            $this->deny($event, Response::HTTP_UNAUTHORIZED, 'unauthorized', 'Invalid or expired session.');

            return;
        }

        $event->getRequest()->attributes->set('app_session', $session);

        if ($session->getStatus() === SessionStatus::AUTHENTICATED || $this->isPartialAuthAllowedPath($event->getRequest())) {
            return;
        }

        $this->deny($event, Response::HTTP_FORBIDDEN, $session->getStatus()->value, 'PIN authorization is required.');
    }

    private function getSessionFromRequest(Request $request): ?Session
    {
        /** @var string|null $token */
        $token = $request->cookies->get(CookieFactory::ACCESS_TOKEN_COOKIE);

        return $token ? $this->sessionManager->findSessionByToken($token) : null;
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
        /** @var string $path */
        $path = $request->getPathInfo();

        return '/' === $path ? $path : rtrim($path, '/');
    }

    private function deny(ControllerEvent $event, int $statusCode, string $status, string $message): void
    {
        $event->setController(static fn (): JsonResponse => new JsonResponse(['status' => $status, 'message' => $message], $statusCode));
    }
}