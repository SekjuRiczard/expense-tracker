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

namespace App\Category\Service;

use App\Category\Dto\Request\CreateCategoryRequest;
use App\Category\Dto\Request\UpdateCategoryRequest;
use App\Category\Dto\Response\CategoryResponse;
use App\Category\Entity\Category;
use App\Category\Exception\CategoryException;
use App\Category\Repository\CategoryRepository;
use App\Entity\User;
use App\Transaction\Repository\TransactionRepository;

final readonly class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private TransactionRepository $transactionRepository,
    ) {
    }

    public function createCategory(CreateCategoryRequest $request, User $user): CategoryResponse
    {
        /** @var Category $category */
        $category = new Category(
            user: $user,
            name: $request->name,
            type: $request->type,
            isDefault: false,
        );

        $this->categoryRepository->save($category);

        return CategoryResponse::fromEntity($category);
    }

    /**
     * @return list<CategoryResponse>
     */
    public function getCategories(User $user): array
    {
        return array_map(
            static fn (Category $category): CategoryResponse => CategoryResponse::fromEntity($category),
            $this->categoryRepository->findDefaultAndUserCategories($user),
        );
    }

    public function getCategory(int $id, User $user): CategoryResponse
    {
        /** @var Category $category */
        $category = $this->categoryRepository->findSingleCategory($id, $user)
            ?? throw CategoryException::notFound();

        return CategoryResponse::fromEntity($category);
    }

    public function updateCategory(int $id, UpdateCategoryRequest $request, User $user): CategoryResponse
    {
        /** @var Category $category */
        $category = $this->categoryRepository->findSingleUserCategory($id, $user)
            ?? throw CategoryException::notFound();

        if (null === $request->name && null === $request->type) {
            throw CategoryException::nothingToUpdate();
        }

        $category->update(
            name: $request->name ?? $category->getName(),
            type: $request->type ?? $category->getType(),
        );

        $this->categoryRepository->save($category);

        return CategoryResponse::fromEntity($category);
    }

    public function deleteCategory(int $id, User $user): void
    {
        /** @var Category $category */
        $category = $this->categoryRepository->findSingleUserCategory($id, $user)
            ?? throw CategoryException::notFound();
        if ($this->transactionRepository->existsForCategory($category)) {
            throw CategoryException::hasTransactions();
        }
        $this->categoryRepository->remove($category);
    }
}
