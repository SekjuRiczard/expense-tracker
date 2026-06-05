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

namespace App\Analytics\Controller;

use App\Analytics\Action\GetBudgetUsageAction;
use App\Analytics\Action\GetCashFlowAction;
use App\Analytics\Action\GetCategoryBreakdownAction;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Analytics\Action\GetPeriodSummaryAction;
use App\Analytics\Dto\Request\AnalyticsPeriodRequest;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use App\Analytics\Action\GetDashboardAction;

#[Route(path: '/api/analytics', name: 'api_analytics_')]
final class AnalyticsController extends AbstractController
{
    public function __construct(
        private readonly GetBudgetUsageAction $getBudgetUsageAction,
        private readonly GetPeriodSummaryAction $getPeriodSummaryAction,
        private readonly GetCategoryBreakdownAction $getCategoryBreakdownAction,
        private readonly GetCashFlowAction $getCashFlowAction,
        private readonly GetDashboardAction $getDashboardAction,
    ) {
    }

    #[Route(
        path: '/budgets/{id}/usage',
        name: 'budget_usage',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    public function getBudgetUsage(int $id): JsonResponse
    {
        return $this->json(
            $this->getBudgetUsageAction->execute(
                $id,
                $this->getAuthenticatedUser(),
            ),
        );
    }

    #[Route(
        path: '/summary',
        name: 'summary',
        methods: ['GET'],
    )]
    public function getSummary(
        #[MapQueryString]
        AnalyticsPeriodRequest $request,
    ): JsonResponse {
        return $this->json(
            $this->getPeriodSummaryAction->execute(
                $request,
                $this->getAuthenticatedUser(),
            ),
        );
    }

    private function getAuthenticatedUser(): User
    {
        /** @var User|null $user */
        $user = $this->getUser();

        return $user ?? throw new AccessDeniedException();
    }

    #[Route(
        path: '/categories',
        name: 'categories',
        methods: ['GET'],
    )]
    public function getCategories(
        #[MapQueryString]
        AnalyticsPeriodRequest $request,
    ): JsonResponse {
        return $this->json(
            $this->getCategoryBreakdownAction->execute(
                $request,
                $this->getAuthenticatedUser(),
            ),
        );
    }
    #[Route(
        path: '/cash-flow',
        name: 'cash_flow',
        methods: ['GET'],
    )]
    public function getCashFlow(
        #[MapQueryString]
        AnalyticsPeriodRequest $request,
    ): JsonResponse {
        return $this->json(
            $this->getCashFlowAction->execute(
                $request,
                $this->getAuthenticatedUser(),
            ),
        );
    }

    #[Route(
        path: '/dashboard',
        name: 'dashboard',
        methods: ['GET'],
    )]
    public function getDashboard(
        #[MapQueryString]
        AnalyticsPeriodRequest $request,
    ): JsonResponse {
        return $this->json(
            $this->getDashboardAction->execute(
                $request,
                $this->getAuthenticatedUser(),
            ),
        );
    }
}