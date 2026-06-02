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

namespace App\Transaction\Controller;

use App\Entity\User;
use App\Transaction\Dto\Request\CreateTransactionRequest;
use App\Transaction\Dto\Request\UpdateTransactionRequest;
use App\Transaction\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/api/transactions', name: 'api_transaction_')]
final class TransactionController extends AbstractController
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    public function createTransaction(#[MapRequestPayload] CreateTransactionRequest $request): JsonResponse
    {
        return $this->json(
            $this->transactionService->createTransaction($request, $this->getAuthenticatedUser()),
            Response::HTTP_CREATED,
        );
    }

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function getTransactions(): JsonResponse
    {
        return $this->json($this->transactionService->getTransactions($this->getAuthenticatedUser()));
    }

    #[Route(path: '/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getTransaction(int $id): JsonResponse
    {
        return $this->json($this->transactionService->getTransaction($id, $this->getAuthenticatedUser()));
    }

    #[Route(path: '/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function updateTransaction(
        int $id,
        #[MapRequestPayload] UpdateTransactionRequest $request,
    ): JsonResponse {
        return $this->json($this->transactionService->updateTransaction($id, $request, $this->getAuthenticatedUser()));
    }

    #[Route(path: '/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteTransaction(int $id): JsonResponse
    {
        $this->transactionService->deleteTransaction($id, $this->getAuthenticatedUser());

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function getAuthenticatedUser(): User
    {
        /** @var User|null $user */
        $user = $this->getUser();

        return $user ?? throw new AccessDeniedException();
    }
}
