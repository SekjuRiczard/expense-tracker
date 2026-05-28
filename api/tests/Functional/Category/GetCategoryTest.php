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

final class GetCategoryTest extends CategoryFunctionalTestCase
{
    public function testAuthenticatedUserCanGetDefaultCategory(): void
    {
        $this->authenticateUser();

        $category = $this->createDefaultCategory(
            name: 'Jedzenie',
            type: CategoryType::EXPENSE,
        );

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $response = $this->getJson(sprintf('/api/categories/%d', $categoryId));

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame($categoryId, $data['id']);
        self::assertSame('Jedzenie', $data['name']);
        self::assertSame('expense', $data['type']);
        self::assertTrue($data['isDefault']);
        self::assertArrayHasKey('createdAt', $data);
        self::assertArrayHasKey('updatedAt', $data);
    }

    public function testAuthenticatedUserCanGetOwnCategory(): void
    {
        $user = $this->authenticateUser();

        $category = $this->createUserCategory(
            user: $user,
            name: 'Subskrypcje',
            type: CategoryType::EXPENSE,
        );

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $response = $this->getJson(sprintf('/api/categories/%d', $categoryId));

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame($categoryId, $data['id']);
        self::assertSame('Subskrypcje', $data['name']);
        self::assertSame('expense', $data['type']);
        self::assertFalse($data['isDefault']);
    }

    public function testAuthenticatedUserCannotGetAnotherUserCategory(): void
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

        $response = $this->getJson(sprintf('/api/categories/%d', $categoryId));

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetMissingCategoryReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/categories/999999');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetCategoryWithInvalidIdReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/categories/abc');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGuestCannotGetCategory(): void
    {
        $category = $this->createDefaultCategory();

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $response = $this->getJson(sprintf('/api/categories/%d', $categoryId));

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}