<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\InvalidLoginCredentialsException;
use App\Exception\TooManyLoginAttemptsException;
use App\Exception\UnauthenticatedUserException;
use App\Exception\UserAlreadyExistsException;
use App\Factory\ApiResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final readonly class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ApiResponseFactory $responseFactory,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = $this->resolveStatusCode($exception);

        if ($statusCode === null) {
            return;
        }

        $event->setResponse($this->responseFactory->errorResponse(
            message: $exception->getMessage(),
            statusCode: $statusCode,
        ));
    }

    private function resolveStatusCode(Throwable $exception): ?int
    {
        return match (true) {
            $exception instanceof UserAlreadyExistsException => Response::HTTP_CONFLICT,
            $exception instanceof InvalidLoginCredentialsException => Response::HTTP_UNAUTHORIZED,
            $exception instanceof TooManyLoginAttemptsException => Response::HTTP_TOO_MANY_REQUESTS,
            $exception instanceof UnauthenticatedUserException => Response::HTTP_UNAUTHORIZED,
            default => null,
        };
    }
}
