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

use App\Category\Entity\Category;
use App\Category\Provider\DefaultCategoryProvider;
use App\Category\Repository\CategoryRepository;

final readonly class DefaultCategoryInitializer
{
    public function __construct(
        private DefaultCategoryProvider $defaultCategoryProvider,
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function initialize(): int
    {
        return array_reduce(
            $this->defaultCategoryProvider->getCategories(),
            function (int $count, array $category): int {
                if (null !== $this->categoryRepository->findDefaultCategory($category['name'], $category['type'])) {
                    return $count;
                }
                $this->categoryRepository->save(new Category(
                    user: null,
                    name: $category['name'],
                    type: $category['type'],
                    isDefault: true,
                ));

                return $count + 1;
            },
            0
        );
    }
}
