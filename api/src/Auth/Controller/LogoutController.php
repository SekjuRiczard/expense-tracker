<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Factory\ApiResponseFactory;
use App\Service\AuthenticatedUserResolver;
use App\Service\CurrentSessionResolver;
use App\Service\SessionManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api', name: 'api_')]
final class LogoutController extends AbstractController
{
    public function __construct(
        private readonly CurrentSessionResolver $currentSessionResolver,
        private readonly SessionManagerInterface $sessionManager,
        private readonly ApiResponseFactory $responseFactory,
        private readonly AuthenticatedUserResolver $authenticatedUserResolver,
    ) {
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(
        Request $request,
        #[CurrentUser] ?User $user,
    ): JsonResponse {
        $user = $this->authenticatedUserResolver->resolve($user);
        $session = $this->currentSessionResolver->resolve($request, $user);

        $this->sessionManager->revokeSession($session);

        return $this->responseFactory->successResponse(
            message: 'Logged out successfully.',
            statusCode: Response::HTTP_OK,
        );
    }
}
