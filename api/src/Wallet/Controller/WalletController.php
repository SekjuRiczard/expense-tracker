<?php

/*
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Wallet\Controller;

use App\Entity\User;
use App\Wallet\Dto\Request\CreateWalletRequest;
use App\Wallet\Dto\Request\UpdateWalletRequest;
use App\Wallet\Service\WalletService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/api/wallets', name: 'api_wallet_')]
final class WalletController extends AbstractController
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    public function createWallet(#[MapRequestPayload] CreateWalletRequest $request): JsonResponse
    {
        return $this->json(
            $this->walletService->createWallet($request, $this->getAuthenticatedUser()),
            Response::HTTP_CREATED,
        );
    }

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function getWallets(): JsonResponse
    {
        return $this->json($this->walletService->getWallets($this->getAuthenticatedUser()));
    }

    #[Route(path: '/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getWallet(int $id): JsonResponse
    {
        return $this->json($this->walletService->getWallet($id, $this->getAuthenticatedUser()));
    }

    #[Route(path: '/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function updateWallet(
        int $id,
        #[MapRequestPayload] UpdateWalletRequest $request,
    ): JsonResponse {
        return $this->json($this->walletService->updateWallet($id, $request, $this->getAuthenticatedUser()));
    }

    #[Route(path: '/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteWallet(int $id): JsonResponse
    {
        $this->walletService->deleteWallet($id, $this->getAuthenticatedUser());

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function getAuthenticatedUser(): User
    {
        /** @var User|null $user */
        $user = $this->getUser();

        return $user ?? throw new AccessDeniedException();
    }
}
