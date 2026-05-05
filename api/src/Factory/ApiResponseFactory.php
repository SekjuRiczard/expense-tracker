<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\AuthTokenResponse;
use App\Dto\UserResponse;
use App\Entity\User;

use App\Service\Token\Transport\JsonBodyTokenTransport;
use App\Service\Token\Transport\TokenTransportInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class ApiResponseFactory
{
    public function __construct(
        #[Autowire(service: JsonBodyTokenTransport::class)]
        private TokenTransportInterface $tokenTransport,
    ) {
    }

    public function tokenResponse(
        AuthTokenResponse $tokenResponse,
        string $message,
        User $user,
        int $statusCode,
    ): JsonResponse {
        $response = new JsonResponse([
            'message' => $message,
            'user' => UserResponse::fromUser($user)->toArray(),
        ], $statusCode);

        return $this->tokenTransport->apply($response, $tokenResponse);
    }

    public function currentUserResponse(
        string $status,
        User $user,
        int $statusCode,
    ): JsonResponse {
        return new JsonResponse([
            'status' => $status,
            'user' => UserResponse::fromUser($user)->toArray(),
        ], $statusCode);
    }

    public function successResponse(string $message, int $statusCode): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => $message,
        ], $statusCode);
    }

    public function errorResponse(string $message, int $statusCode, string $status = 'error'): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'message' => $message,
        ], $statusCode);
    }

    public function unauthenticatedResponse(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'unauthenticated',
            'user' => null,
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
