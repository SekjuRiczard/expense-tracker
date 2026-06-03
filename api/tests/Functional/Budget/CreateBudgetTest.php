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

namespace App\Tests\Functional\Budget;

use App\Budget\Entity\Budget;
use App\Budget\Enum\BudgetPeriodType;
use App\Tests\Support\BudgetFunctionalTestCase;
use App\Wallet\Enum\CurrencyCode;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class CreateBudgetTest extends BudgetFunctionalTestCase
{
    public function testAuthenticatedUserCanCreateMonthlyBudget(): void
    {
        $user = $this->authenticateUser();

        $response = $this->postJson('/api/budgets', [
            'name' => 'Budżet domowy — czerwiec',
            'amount' => 400000,
            'currency' => 'PLN',
            'periodType' => 'monthly',
            'startDate' => '2026-06-01',
            'endDate' => '2026-06-30',
        ]);

        self::assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertIsInt($data['id']);
        self::assertSame('Budżet domowy — czerwiec', $data['name']);
        self::assertSame(400000, $data['amount']);
        self::assertSame('PLN', $data['currency']);
        self::assertSame('monthly', $data['periodType']);
        self::assertSame('2026-06-01', $data['startDate']);
        self::assertSame('2026-06-30', $data['endDate']);
        self::assertArrayHasKey('createdAt', $data);
        self::assertArrayHasKey('updatedAt', $data);

        $budget = $this->findBudgetFresh($data['id']);

        self::assertInstanceOf(Budget::class, $budget);
        self::assertSame(
            (string) $user->getId(),
            (string) $budget->getUser()->getId(),
        );
        self::assertSame('Budżet domowy — czerwiec', $budget->getName());
        self::assertSame(400000, $budget->getAmount());
        self::assertSame(CurrencyCode::PLN, $budget->getCurrency());
        self::assertSame(
            BudgetPeriodType::MONTHLY,
            $budget->getPeriodType(),
        );
        self::assertSame('2026-06-01', $budget->getStartDate()->format('Y-m-d'));
        self::assertSame('2026-06-30', $budget->getEndDate()->format('Y-m-d'));
    }

    public function testAuthenticatedUserCanCreateYearlyBudget(): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/budgets', [
            'name' => 'Budżet roczny 2026',
            'amount' => 4800000,
            'currency' => 'PLN',
            'periodType' => 'yearly',
            'startDate' => '2026-01-01',
            'endDate' => '2026-12-31',
        ]);

        self::assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame('Budżet roczny 2026', $data['name']);
        self::assertSame(4800000, $data['amount']);
        self::assertSame('yearly', $data['periodType']);
        self::assertSame('2026-01-01', $data['startDate']);
        self::assertSame('2026-12-31', $data['endDate']);
    }

    public function testAuthenticatedUserCanCreateCustomBudget(): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/budgets', [
            'name' => 'Wakacje',
            'amount' => 800000,
            'currency' => 'EUR',
            'periodType' => 'custom',
            'startDate' => '2026-07-10',
            'endDate' => '2026-08-18',
        ]);

        self::assertSame(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
        );

        $data = $this->jsonResponse();

        self::assertSame('Wakacje', $data['name']);
        self::assertSame(800000, $data['amount']);
        self::assertSame('EUR', $data['currency']);
        self::assertSame('custom', $data['periodType']);
        self::assertSame('2026-07-10', $data['startDate']);
        self::assertSame('2026-08-18', $data['endDate']);
    }

    public function testAuthenticatedUserCanCreateBudgetsForDifferentCurrencies(): void
    {
        $this->authenticateUser();

        $plnResponse = $this->postJson('/api/budgets', [
            'name' => 'Budżet PLN',
            'amount' => 300000,
            'currency' => 'PLN',
            'periodType' => 'monthly',
            'startDate' => '2026-06-01',
            'endDate' => '2026-06-30',
        ]);

        self::assertSame(
            Response::HTTP_CREATED,
            $plnResponse->getStatusCode(),
        );

        $eurResponse = $this->postJson('/api/budgets', [
            'name' => 'Budżet EUR',
            'amount' => 100000,
            'currency' => 'EUR',
            'periodType' => 'monthly',
            'startDate' => '2026-06-01',
            'endDate' => '2026-06-30',
        ]);

        self::assertSame(
            Response::HTTP_CREATED,
            $eurResponse->getStatusCode(),
        );
    }

    public function testCannotCreateDuplicateBudgetForTheSamePeriod(): void
    {
        $this->authenticateUser();

        $this->createBudgetThroughApi();

        $response = $this->postJson('/api/budgets', [
            'name' => 'Duplikat',
            'amount' => 500000,
            'currency' => 'PLN',
            'periodType' => 'monthly',
            'startDate' => self::DEFAULT_START_DATE,
            'endDate' => self::DEFAULT_END_DATE,
        ]);

        self::assertSame(
            Response::HTTP_CONFLICT,
            $response->getStatusCode(),
        );
    }

    public function testGuestCannotCreateBudget(): void
    {
        $response = $this->postJson('/api/budgets', [
            'name' => 'Budżet domowy',
            'amount' => 300000,
            'currency' => 'PLN',
            'periodType' => 'monthly',
            'startDate' => self::DEFAULT_START_DATE,
            'endDate' => self::DEFAULT_END_DATE,
        ]);

        self::assertSame(
            Response::HTTP_UNAUTHORIZED,
            $response->getStatusCode(),
        );

        $budgets = $this->entityManager
            ->getRepository(Budget::class)
            ->findAll();

        self::assertCount(0, $budgets);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('invalidPayloadProvider')]
    public function testCreateBudgetWithInvalidPayloadReturnsValidationError(
        array $payload,
    ): void {
        $this->authenticateUser();

        $response = $this->postJson('/api/budgets', $payload);

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );

        $budgets = $this->entityManager
            ->getRepository(Budget::class)
            ->findAll();

        self::assertCount(0, $budgets);
    }

    public function testCannotCreateBudgetWithInvalidDateRange(): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/budgets', [
            'name' => 'Niepoprawny zakres',
            'amount' => 300000,
            'currency' => 'PLN',
            'periodType' => 'custom',
            'startDate' => '2026-08-01',
            'endDate' => '2026-07-01',
        ]);

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testCannotCreateMonthlyBudgetForPartialMonth(): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/budgets', [
            'name' => 'Niepełny miesiąc',
            'amount' => 300000,
            'currency' => 'PLN',
            'periodType' => 'monthly',
            'startDate' => '2026-06-05',
            'endDate' => '2026-06-30',
        ]);

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testCannotCreateYearlyBudgetForPartialYear(): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/budgets', [
            'name' => 'Niepełny rok',
            'amount' => 300000,
            'currency' => 'PLN',
            'periodType' => 'yearly',
            'startDate' => '2026-01-01',
            'endDate' => '2026-11-30',
        ]);

        self::assertSame(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $response->getStatusCode(),
        );
    }

    public function testCreateBudgetWithMalformedJsonReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $response = $this->postMalformedJson(
            '/api/budgets',
            '{"name": "Budżet domowy"',
        );

        self::assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
        );
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>}>
     */
    public static function invalidPayloadProvider(): iterable
    {
        yield 'blank name' => [
            'payload' => [
                'name' => '',
                'amount' => 300000,
                'currency' => 'PLN',
                'periodType' => 'monthly',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'too long name' => [
            'payload' => [
                'name' => str_repeat('a', 101),
                'amount' => 300000,
                'currency' => 'PLN',
                'periodType' => 'monthly',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'zero amount' => [
            'payload' => [
                'name' => 'Budżet domowy',
                'amount' => 0,
                'currency' => 'PLN',
                'periodType' => 'monthly',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'negative amount' => [
            'payload' => [
                'name' => 'Budżet domowy',
                'amount' => -1,
                'currency' => 'PLN',
                'periodType' => 'monthly',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'invalid currency' => [
            'payload' => [
                'name' => 'Budżet domowy',
                'amount' => 300000,
                'currency' => 'JPY',
                'periodType' => 'monthly',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'invalid period type' => [
            'payload' => [
                'name' => 'Budżet domowy',
                'amount' => 300000,
                'currency' => 'PLN',
                'periodType' => 'weekly',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'missing name' => [
            'payload' => [
                'amount' => 300000,
                'currency' => 'PLN',
                'periodType' => 'monthly',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'missing amount' => [
            'payload' => [
                'name' => 'Budżet domowy',
                'currency' => 'PLN',
                'periodType' => 'monthly',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'missing currency' => [
            'payload' => [
                'name' => 'Budżet domowy',
                'amount' => 300000,
                'periodType' => 'monthly',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'missing period type' => [
            'payload' => [
                'name' => 'Budżet domowy',
                'amount' => 300000,
                'currency' => 'PLN',
                'startDate' => '2026-06-01',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'missing start date' => [
            'payload' => [
                'name' => 'Budżet domowy',
                'amount' => 300000,
                'currency' => 'PLN',
                'periodType' => 'monthly',
                'endDate' => '2026-06-30',
            ],
        ];

        yield 'missing end date' => [
            'payload' => [
                'name' => 'Budżet domowy',
                'amount' => 300000,
                'currency' => 'PLN',
                'periodType' => 'monthly',
                'startDate' => '2026-06-01',
            ],
        ];
    }
}