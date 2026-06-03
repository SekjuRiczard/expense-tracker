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

final class ListCategoriesTest extends CategoryFunctionalTestCase
{
    public function testAuthenticatedUserCanListDefaultAndOwnCategories(): void
    {
        $user = $this->authenticateUser();

        $this->createDefaultCategory(
            name: 'Jedzenie',
            type: CategoryType::EXPENSE,
        );

        $this->createUserCategory(
            user: $user,
            name: 'Subskrypcje',
            type: CategoryType::EXPENSE,
        );

        $response = $this->getJson('/api/categories');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertCount(2, $data);

        $names = array_column($data, 'name');

        self::assertContains('Jedzenie', $names);
        self::assertContains('Subskrypcje', $names);
    }

    public function testAuthenticatedUserCannotListAnotherUserCategories(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $this->createUserCategory(
            user: $owner,
            name: 'Cudza kategoria',
        );

        $this->authenticateUser(
            email: 'viewer@example.com',
            username: 'viewer',
        );

        $response = $this->getJson('/api/categories');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();
        $names = array_column($data, 'name');

        self::assertNotContains('Cudza kategoria', $names);
    }

    public function testAuthenticatedUserWithoutCategoriesReceivesEmptyList(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/categories');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame([], $data);
    }

    public function testGuestCannotListCategories(): void
    {
        $response = $this->getJson('/api/categories');

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
