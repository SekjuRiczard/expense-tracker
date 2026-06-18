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

namespace App\Auth\Controller;

use App\Entity\Session;
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
    ) {
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(
        Request $request,
        #[CurrentUser] ?User $user,
    ): JsonResponse {
        if (!$user instanceof User) {
            return $this->json([
                'status' => 'unauthenticated',
                'user' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }
        /** @var Session $session */
        $session = $this->currentSessionResolver->resolve($request, $user);

        return $this->json([
            'status' => $session->getStatus()->value,
            'user' => [
                'id' => (string) $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'hasPin' => null !== $user->getPin(),
                'roles' => $user->getRoles(),
            ],
        ], Response::HTTP_OK);
    }
}
