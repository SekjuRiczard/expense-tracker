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

namespace App\Tests\Functional\Category;

use App\Category\Enum\CategoryType;
use App\Tests\Support\CategoryFunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class DeleteCategoryTest extends CategoryFunctionalTestCase
{
    public function testAuthenticatedUserCanDeleteOwnCategory(): void
    {
        $user = $this->authenticateUser();

        $category = $this->createUserCategory(
            user: $user,
            name: 'Subskrypcje',
        );

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $response = $this->deleteJson(sprintf('/api/categories/%d', $categoryId));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertSame('', $response->getContent());
        self::assertNull($this->findCategoryFresh($categoryId));
    }

    public function testAuthenticatedUserCannotDeleteDefaultCategory(): void
    {
        $this->authenticateUser();

        $category = $this->createDefaultCategory(
            name: 'Jedzenie',
            type: CategoryType::EXPENSE,
        );

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $response = $this->deleteJson(sprintf('/api/categories/%d', $categoryId));

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertNotNull($this->findCategoryFresh($categoryId));
    }

    public function testAuthenticatedUserCannotDeleteAnotherUserCategory(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $category = $this->createUserCategory(
            user: $owner,
            name: 'Cudza kategoria',
        );

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $this->authenticateUser(
            email: 'intruder@example.com',
            username: 'intruder',
        );

        $response = $this->deleteJson(sprintf('/api/categories/%d', $categoryId));

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertNotNull($this->findCategoryFresh($categoryId));
    }

    public function testDeleteMissingCategoryReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->deleteJson('/api/categories/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGuestCannotDeleteCategory(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $category = $this->createUserCategory(
            user: $owner,
            name: 'Subskrypcje',
        );

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $response = $this->deleteJson(sprintf('/api/categories/%d', $categoryId));

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertNotNull($this->findCategoryFresh($categoryId));
    }

    public function testDeletingOneCategoryDoesNotDeleteAnotherCategory(): void
    {
        $user = $this->authenticateUser();

        $categoryToDelete = $this->createUserCategory(
            user: $user,
            name: 'Do usunięcia',
        );

        $categoryToKeep = $this->createUserCategory(
            user: $user,
            name: 'Ma zostać',
        );

        $categoryToDeleteId = $categoryToDelete->getId();
        $categoryToKeepId = $categoryToKeep->getId();

        self::assertIsInt($categoryToDeleteId);
        self::assertIsInt($categoryToKeepId);

        $response = $this->deleteJson(sprintf('/api/categories/%d', $categoryToDeleteId));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertNull($this->findCategoryFresh($categoryToDeleteId));
        self::assertNotNull($this->findCategoryFresh($categoryToKeepId));
    }
}