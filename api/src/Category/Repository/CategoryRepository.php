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

namespace App\Category\Repository;

use App\Category\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Category\Enum\CategoryType;

final class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $category): void
    {
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
    }

    public function remove(Category $category): void
    {
        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();
    }

    /**
     * @return list<Category>
     */
    public function findDefaultAndUserCategories(User $user): array
    {
        return [
            ...$this->findBy(
                ['user' => null],
                ['name' => 'ASC'],
            ),
            ...$this->findBy(
                ['user' => $user],
                ['name' => 'ASC'],
            ),
        ];
    }

    public function findSingleCategory(int $id, User $user): ?Category
    {
        /** @var Category|null $category */
        $category = $this->find($id);

        return $category instanceof Category
        && ($this->isDefaultCategory($category) || $this->isUserCategory($category, $user))
            ? $category
            : null;
    }

    public function findSingleUserCategory(int $id, User $user): ?Category
    {
        /** @var Category|null $category */
        $category = $this->find($id);

        return $category instanceof Category
        && !$this->isDefaultCategory($category)
        && $this->isUserCategory($category, $user)
            ? $category
            : null;
    }

    public function findDefaultCategory(string $name, CategoryType $type): ?Category
    {
        return $this->findOneBy([
            'user' => null,
            'name' => $name,
            'type' => $type,
            'isDefault' => true,
        ]);
    }

    private function isDefaultCategory(Category $category): bool
    {
        return null === $category->getUser();
    }

    private function isUserCategory(Category $category, User $user): bool
    {
        return (string) $category->getUser()?->getId() === (string) $user->getId();
    }
}