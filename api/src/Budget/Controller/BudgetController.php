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

namespace App\Budget\Controller;

use App\Budget\Action\CreateBudgetAction;
use App\Budget\Action\DeleteBudgetAction;
use App\Budget\Action\GetBudgetAction;
use App\Budget\Action\ListBudgetsAction;
use App\Budget\Action\ListBudgetsOverviewAction;
use App\Budget\Action\UpdateBudgetAction;
use App\Budget\Dto\Request\CreateBudgetRequest;
use App\Budget\Dto\Request\UpdateBudgetRequest;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/api/budgets', name: 'api_budget_')]
final class BudgetController extends AbstractController
{
    public function __construct(
        private readonly CreateBudgetAction $createBudgetAction,
        private readonly ListBudgetsAction $listBudgetsAction,
        private readonly ListBudgetsOverviewAction $listBudgetsOverviewAction,
        private readonly GetBudgetAction $getBudgetAction,
        private readonly UpdateBudgetAction $updateBudgetAction,
        private readonly DeleteBudgetAction $deleteBudgetAction,
    ) {
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    public function createBudget(
        #[MapRequestPayload]
        CreateBudgetRequest $request,
    ): JsonResponse {
        return $this->json(
            $this->createBudgetAction->execute(
                $request,
                $this->getAuthenticatedUser(),
            ),
            Response::HTTP_CREATED,
        );
    }

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function getBudgets(): JsonResponse
    {
        return $this->json(
            $this->listBudgetsAction->execute(
                $this->getAuthenticatedUser(),
            ),
        );
    }

    #[Route(path: '/overview', name: 'overview', methods: ['GET'])]
    public function getBudgetsOverview(): JsonResponse
    {
        return $this->json(
            $this->listBudgetsOverviewAction->execute(
                $this->getAuthenticatedUser(),
            ),
        );
    }

    #[Route(
        path: '/{id}',
        name: 'show',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    public function getBudget(int $id): JsonResponse
    {
        return $this->json(
            $this->getBudgetAction->execute(
                $id,
                $this->getAuthenticatedUser(),
            ),
        );
    }

    #[Route(
        path: '/{id}',
        name: 'update',
        requirements: ['id' => '\d+'],
        methods: ['PATCH'],
    )]
    public function updateBudget(
        int $id,
        #[MapRequestPayload]
        UpdateBudgetRequest $request,
    ): JsonResponse {
        return $this->json(
            $this->updateBudgetAction->execute(
                $id,
                $request,
                $this->getAuthenticatedUser(),
            ),
        );
    }

    #[Route(
        path: '/{id}',
        name: 'delete',
        requirements: ['id' => '\d+'],
        methods: ['DELETE'],
    )]
    public function deleteBudget(int $id): JsonResponse
    {
        $this->deleteBudgetAction->execute(
            $id,
            $this->getAuthenticatedUser(),
        );

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function getAuthenticatedUser(): User
    {
        /** @var User|null $user */
        $user = $this->getUser();

        return $user ?? throw new AccessDeniedException();
    }
}
