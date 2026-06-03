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

namespace App\Category\Controller;

use App\Category\Dto\Request\CreateCategoryRequest;
use App\Category\Dto\Request\UpdateCategoryRequest;
use App\Category\Service\CategoryService;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/api/categories', name: 'api_category_')]
final class CategoryController extends AbstractController
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    public function createCategory(#[MapRequestPayload] CreateCategoryRequest $request): JsonResponse
    {
        return $this->json(
            $this->categoryService->createCategory($request, $this->getAuthenticatedUser()),
            Response::HTTP_CREATED,
        );
    }

    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function getCategories(): JsonResponse
    {
        return $this->json(
            $this->categoryService->getCategories($this->getAuthenticatedUser()),
        );
    }

    #[Route(path: '/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getCategory(int $id): JsonResponse
    {
        return $this->json(
            $this->categoryService->getCategory($id, $this->getAuthenticatedUser()),
        );
    }

    #[Route(path: '/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    public function updateCategory(
        int $id,
        #[MapRequestPayload] UpdateCategoryRequest $request,
    ): JsonResponse {
        return $this->json(
            $this->categoryService->updateCategory($id, $request, $this->getAuthenticatedUser()),
        );
    }

    #[Route(path: '/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteCategory(int $id): JsonResponse
    {
        $this->categoryService->deleteCategory($id, $this->getAuthenticatedUser());

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function getAuthenticatedUser(): User
    {
        /** @var User|null $user */
        $user = $this->getUser();

        return $user ?? throw new AccessDeniedException();
    }
}
