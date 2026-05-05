<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Auth\Factory\ApiResponseFactory;
use App\Entity\User;
use App\Session\Service\CurrentSessionResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api', name: 'api_')]
final class MeController extends AbstractController
{
    public function __construct(
        private readonly CurrentSessionResolver $currentSessionResolver,
        private readonly ApiResponseFactory $responseFactory,
    ) {
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(
        Request $request,
        #[CurrentUser] ?User $user,
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->responseFactory->unauthenticatedResponse();
        }

        $session = $this->currentSessionResolver->resolve($request, $user);

        return $this->responseFactory->currentUserResponse(
            status: $session->getStatus()->value,
            user: $user,
            statusCode: Response::HTTP_OK,
        );
    }
}
