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

namespace App\DemoData\Service;

use App\Budget\Action\CreateBudgetAction;
use App\Budget\Dto\Request\CreateBudgetRequest;
use App\Budget\Entity\Budget;
use App\Budget\Enum\BudgetPeriodType;
use App\Category\Entity\Category;
use App\Category\Repository\CategoryRepository;
use App\Category\Service\DefaultCategoryInitializer;
use App\DemoData\Dto\Response\GenerateDemoDataResponse;
use App\DemoData\Entity\DemoDataBatch;
use App\DemoData\Entity\DemoDataRecord;
use App\DemoData\Exception\DemoDataException;
use App\DemoData\Repository\DemoDataBatchRepository;
use App\DemoData\Repository\DemoDataRecordRepository;
use App\Entity\User;
use App\Transaction\Action\CreateTransactionAction;
use App\Transaction\Dto\Request\CreateTransactionRequest;
use App\Transaction\Entity\Transaction;
use App\Transaction\Enum\TransactionType;
use App\Wallet\Entity\Wallet;
use App\Wallet\Enum\CurrencyCode;
use App\Wallet\Enum\WalletType;
use App\Wallet\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\Engine\Mt19937;
use Random\Randomizer;

final readonly class DemoDataGenerator
{
    private const MONTHS_TO_GENERATE = 12;

    private const MIN_VARIABLE_EXPENSES_PER_MONTH = 25;

    private const MAX_VARIABLE_EXPENSES_PER_MONTH = 45;

    private const MIN_EXTRA_INCOMES_PER_MONTH = 0;

    private const MAX_EXTRA_INCOMES_PER_MONTH = 2;

    public function __construct(
        private DefaultCategoryInitializer $defaultCategoryInitializer,
        private CategoryRepository $categoryRepository,
        private WalletRepository $walletRepository,
        private CreateBudgetAction $createBudgetAction,
        private CreateTransactionAction $createTransactionAction,
        private DemoDataBatchRepository $demoDataBatchRepository,
        private DemoDataRecordRepository $demoDataRecordRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function generate(User $user, ?int $seed = null): GenerateDemoDataResponse
    {
        /** @var int $resolvedSeed */
        $resolvedSeed = $seed ?? random_int(1, 2_147_483_647);
        /** @var Randomizer $randomizer */
        $randomizer = new Randomizer(new Mt19937($resolvedSeed));

        return $this->entityManager->wrapInTransaction(
            function () use ($user, $resolvedSeed, $randomizer): GenerateDemoDataResponse {
                $this->assertNoActiveBatch($user);
                /** @var int $defaultCategoriesCreated */
                $defaultCategoriesCreated = $this->defaultCategoryInitializer->initialize();
                /** @var array<string, Wallet> $wallets */
                $wallets = $this->createWallets($user);
                /** @var array<string, Category> $categories */
                $categories = $this->getCategoriesIndexedByName($user);
                /** @var list<int> $budgetIds */
                $budgetIds = $this->createMonthlyBudgets(
                    user: $user,
                    randomizer: $randomizer,
                );
                /** @var list<int> $transactionIds */
                $transactionIds = $this->createTransactions(
                    user: $user,
                    wallets: $wallets,
                    categories: $categories,
                    randomizer: $randomizer,
                );
                /** @var list<int> $walletIds */
                $walletIds = array_map(
                    static fn (Wallet $wallet): int => $wallet->getId()
                        ?? throw new \LogicException('Demo wallet ID is required.'),
                    array_values($wallets),
                );

                $batch = new DemoDataBatch(
                    user: $user,
                    seed: $resolvedSeed,
                    walletsCount: count($walletIds),
                    budgetsCount: count($budgetIds),
                    transactionsCount: count($transactionIds),
                );
                $this->demoDataBatchRepository->add($batch);
                $this->recordEntities($batch, Wallet::class, $walletIds);
                $this->recordEntities($batch, Budget::class, $budgetIds);
                $this->recordEntities($batch, Transaction::class, $transactionIds);
                $this->entityManager->flush();

                return new GenerateDemoDataResponse(
                    seed: $resolvedSeed,
                    monthsGenerated: self::MONTHS_TO_GENERATE,
                    defaultCategoriesCreated: $defaultCategoriesCreated,
                    walletsCreated: count($walletIds),
                    budgetsCreated: count($budgetIds),
                    transactionsCreated: count($transactionIds),
                    demoDataExists: true,
                );
            },
        );
    }

    private function assertNoActiveBatch(User $user): void
    {
        if (null !== $this->demoDataBatchRepository->findActiveByUser($user)) {
            throw DemoDataException::alreadyGenerated();
        }
    }

    /**
     * @param class-string $entityClass
     * @param list<int>    $entityIds
     */
    private function recordEntities(
        DemoDataBatch $batch,
        string $entityClass,
        array $entityIds,
    ): void {
        foreach ($entityIds as $entityId) {
            $this->demoDataRecordRepository->add(
                new DemoDataRecord($batch, $entityClass, $entityId),
            );
        }
    }

    /**
     * @return array<string, Wallet>
     */
    private function createWallets(User $user): array
    {
        /** @var array<string, Wallet> $wallets */
        $wallets = [
            'main' => new Wallet(
                user: $user,
                name: 'Konto główne',
                type: WalletType::BANK_ACCOUNT,
                currency: CurrencyCode::PLN,
                balanceAmount: 450_000,
            ),
            'cash' => new Wallet(
                user: $user,
                name: 'Gotówka',
                type: WalletType::CASH,
                currency: CurrencyCode::PLN,
                balanceAmount: 500_000,
            ),
            'savings' => new Wallet(
                user: $user,
                name: 'Oszczędności',
                type: WalletType::SAVINGS_ACCOUNT,
                currency: CurrencyCode::PLN,
                balanceAmount: 2_000_000,
            ),
            'card' => new Wallet(
                user: $user,
                name: 'Karta kredytowa',
                type: WalletType::CREDIT_CARD,
                currency: CurrencyCode::PLN,
                balanceAmount: 0,
            ),
        ];
        /** @var Wallet $wallet */
        foreach ($wallets as $wallet) {
            $this->walletRepository->save($wallet);
        }

        return $wallets;
    }

    /**
     * @return array<string, Category>
     */
    private function getCategoriesIndexedByName(User $user): array
    {
        /** @var array<string, Category> $categories */
        $categories = [];
        /** @var Category $category */
        foreach ($this->categoryRepository->findDefaultAndUserCategories($user) as $category) {
            $categories[$category->getName()] = $category;
        }

        return $categories;
    }

    /**
     * @return list<int>
     */
    private function createMonthlyBudgets(
        User $user,
        Randomizer $randomizer,
    ): array {
        /** @var list<int> $budgetIds */
        $budgetIds = [];
        /** @var \DateTimeImmutable $currentMonth */
        $currentMonth = new \DateTimeImmutable('first day of this month 00:00:00');
        for ($offset = self::MONTHS_TO_GENERATE - 1; $offset >= 0; --$offset) {
            /** @var \DateTimeImmutable $startDate */
            $startDate = $currentMonth->modify(sprintf('-%d months', $offset));
            /** @var \DateTimeImmutable $endDate */
            $endDate = $startDate->modify('last day of this month 23:59:59');
            $budgetIds[] = $this->createBudgetAction->execute(
                request: new CreateBudgetRequest(
                    name: sprintf('Budżet %s', $startDate->format('m/Y')),
                    amount: $randomizer->getInt(500_000, 750_000),
                    currency: CurrencyCode::PLN,
                    periodType: BudgetPeriodType::MONTHLY,
                    startDate: $startDate,
                    endDate: $endDate,
                ),
                user: $user,
            )->id;
        }

        return $budgetIds;
    }

    /**
     * @param array<string, Wallet>   $wallets
     * @param array<string, Category> $categories
     */
    /**
     * @return list<int>
     */
    private function createTransactions(
        User $user,
        array $wallets,
        array $categories,
        Randomizer $randomizer,
    ): array {
        /** @var list<int> $transactionIds */
        $transactionIds = [];
        /** @var \DateTimeImmutable $currentMonth */
        $currentMonth = new \DateTimeImmutable('first day of this month 00:00:00');
        for ($offset = self::MONTHS_TO_GENERATE - 1; $offset >= 0; --$offset) {
            /** @var \DateTimeImmutable $month */
            $month = $currentMonth->modify(sprintf('-%d months', $offset));
            $transactionIds = [
                ...$transactionIds,
                ...$this->createSalaryTransaction(
                    user: $user,
                    wallet: $wallets['main'],
                    categories: $categories,
                    month: $month,
                    randomizer: $randomizer,
                ),
                ...$this->createRecurringExpenseTransactions(
                    user: $user,
                    wallets: $wallets,
                    categories: $categories,
                    month: $month,
                    randomizer: $randomizer,
                ),
                ...$this->createExtraIncomeTransactions(
                    user: $user,
                    wallet: $wallets['main'],
                    categories: $categories,
                    month: $month,
                    randomizer: $randomizer,
                ),
                ...$this->createVariableExpenseTransactions(
                    user: $user,
                    wallets: $wallets,
                    categories: $categories,
                    month: $month,
                    randomizer: $randomizer,
                ),
            ];
        }

        return $transactionIds;
    }

    /**
     * @param array<string, Category> $categories
     */
    /**
     * @return list<int>
     */
    private function createSalaryTransaction(
        User $user,
        Wallet $wallet,
        array $categories,
        \DateTimeImmutable $month,
        Randomizer $randomizer,
    ): array {
        return [
            $this->createTransaction(
                user: $user,
                wallet: $wallet,
                category: $this->getRequiredCategory($categories, 'Pensja'),
                type: TransactionType::INCOME,
                amount: $randomizer->getInt(680_000, 950_000),
                title: 'Wynagrodzenie',
                transactionDate: $this->randomDateInMonth(
                    month: $month,
                    randomizer: $randomizer,
                    preferredDay: 1,
                ),
            ),
        ];
    }

    /**
     * @param array<string, Wallet>   $wallets
     * @param array<string, Category> $categories
     */
    /**
     * @return list<int>
     */
    private function createRecurringExpenseTransactions(
        User $user,
        array $wallets,
        array $categories,
        \DateTimeImmutable $month,
        Randomizer $randomizer,
    ): array {
        /** @var list<int> $transactionIds */
        $transactionIds = [];
        /** @var list<array{category: string, title: string, minAmount: int, maxAmount: int, day: int}> $expenses */
        $expenses = [
            [
                'category' => 'Opłaty i rachunki',
                'title' => 'Czynsz',
                'minAmount' => 180_000,
                'maxAmount' => 260_000,
                'day' => 3,
            ],
            [
                'category' => 'Opłaty i rachunki',
                'title' => 'Rachunek za prąd',
                'minAmount' => 12_000,
                'maxAmount' => 35_000,
                'day' => 7,
            ],
            [
                'category' => 'Opłaty i rachunki',
                'title' => 'Internet i telefon',
                'minAmount' => 7_000,
                'maxAmount' => 16_000,
                'day' => 9,
            ],
            [
                'category' => 'Rozrywka',
                'title' => 'Subskrypcje streamingowe',
                'minAmount' => 3_000,
                'maxAmount' => 9_000,
                'day' => 12,
            ],
            [
                'category' => 'Zdrowie i uroda',
                'title' => 'Karnet na siłownię',
                'minAmount' => 9_000,
                'maxAmount' => 18_000,
                'day' => 15,
            ],
        ];
        /** @var array{category: string, title: string, minAmount: int, maxAmount: int, day: int} $expense */
        foreach ($expenses as $expense) {
            $transactionIds[] = $this->createTransaction(
                user: $user,
                wallet: $wallets['main'],
                category: $this->getRequiredCategory($categories, $expense['category']),
                type: TransactionType::EXPENSE,
                amount: $randomizer->getInt(
                    $expense['minAmount'],
                    $expense['maxAmount'],
                ),
                title: $expense['title'],
                transactionDate: $this->randomDateInMonth(
                    month: $month,
                    randomizer: $randomizer,
                    preferredDay: $expense['day'],
                ),
            );
        }

        return $transactionIds;
    }

    /**
     * @param array<string, Category> $categories
     */
    /**
     * @return list<int>
     */
    private function createExtraIncomeTransactions(
        User $user,
        Wallet $wallet,
        array $categories,
        \DateTimeImmutable $month,
        Randomizer $randomizer,
    ): array {
        /** @var list<int> $transactionIds */
        $transactionIds = [];
        /** @var list<array{category: string, title: string, minAmount: int, maxAmount: int}> $templates */
        $templates = [
            [
                'category' => 'Freelance',
                'title' => 'Dodatkowe zlecenie',
                'minAmount' => 20_000,
                'maxAmount' => 180_000,
            ],
            [
                'category' => 'Premia',
                'title' => 'Premia uznaniowa',
                'minAmount' => 15_000,
                'maxAmount' => 120_000,
            ],
            [
                'category' => 'Zwrot',
                'title' => 'Zwrot środków',
                'minAmount' => 5_000,
                'maxAmount' => 60_000,
            ],
            [
                'category' => 'Sprzedaż',
                'title' => 'Sprzedaż używanego przedmiotu',
                'minAmount' => 10_000,
                'maxAmount' => 150_000,
            ],
        ];
        /** @var int $transactionsToCreate */
        $transactionsToCreate = $randomizer->getInt(
            self::MIN_EXTRA_INCOMES_PER_MONTH,
            self::MAX_EXTRA_INCOMES_PER_MONTH,
        );
        for ($index = 0; $index < $transactionsToCreate; ++$index) {
            /** @var array{category: string, title: string, minAmount: int, maxAmount: int} $template */
            $template = $this->randomItem($templates, $randomizer);
            $transactionIds[] = $this->createTransaction(
                user: $user,
                wallet: $wallet,
                category: $this->getRequiredCategory($categories, $template['category']),
                type: TransactionType::INCOME,
                amount: $randomizer->getInt(
                    $template['minAmount'],
                    $template['maxAmount'],
                ),
                title: $template['title'],
                transactionDate: $this->randomDateInMonth(
                    month: $month,
                    randomizer: $randomizer,
                ),
            );
        }

        return $transactionIds;
    }

    /**
     * @param array<string, Wallet>   $wallets
     * @param array<string, Category> $categories
     */
    /**
     * @return list<int>
     */
    private function createVariableExpenseTransactions(
        User $user,
        array $wallets,
        array $categories,
        \DateTimeImmutable $month,
        Randomizer $randomizer,
    ): array {
        /** @var list<int> $transactionIds */
        $transactionIds = [];
        /** @var list<array{category: string, title: string, minAmount: int, maxAmount: int}> $templates */
        $templates = [
            [
                'category' => 'Jedzenie i chemia',
                'title' => 'Zakupy spożywcze',
                'minAmount' => 3_500,
                'maxAmount' => 25_000,
            ],
            [
                'category' => 'Jedzenie i chemia',
                'title' => 'Restauracja',
                'minAmount' => 4_000,
                'maxAmount' => 18_000,
            ],
            [
                'category' => 'Jedzenie i chemia',
                'title' => 'Kawa na mieście',
                'minAmount' => 1_200,
                'maxAmount' => 4_000,
            ],
            [
                'category' => 'Transport',
                'title' => 'Paliwo',
                'minAmount' => 12_000,
                'maxAmount' => 35_000,
            ],
            [
                'category' => 'Transport',
                'title' => 'Komunikacja miejska',
                'minAmount' => 3_000,
                'maxAmount' => 12_000,
            ],
            [
                'category' => 'Zdrowie i uroda',
                'title' => 'Apteka',
                'minAmount' => 2_500,
                'maxAmount' => 18_000,
            ],
            [
                'category' => 'Rozrywka',
                'title' => 'Kino',
                'minAmount' => 3_000,
                'maxAmount' => 9_000,
            ],
            [
                'category' => 'Zakupy',
                'title' => 'Zakupy odzieżowe',
                'minAmount' => 5_000,
                'maxAmount' => 35_000,
            ],
            [
                'category' => 'Edukacja i rozwój',
                'title' => 'Kurs online',
                'minAmount' => 5_000,
                'maxAmount' => 45_000,
            ],
            [
                'category' => 'Usługi',
                'title' => 'Fryzjer',
                'minAmount' => 4_000,
                'maxAmount' => 18_000,
            ],
            [
                'category' => 'Zakupy',
                'title' => 'Wyposażenie domu',
                'minAmount' => 3_000,
                'maxAmount' => 30_000,
            ],
            [
                'category' => 'Inne / Nieprzewidziane wydatki',
                'title' => 'Nieprzewidziany wydatek',
                'minAmount' => 5_000,
                'maxAmount' => 50_000,
            ],
        ];
        /** @var int $transactionsToCreate */
        $transactionsToCreate = $randomizer->getInt(
            self::MIN_VARIABLE_EXPENSES_PER_MONTH,
            self::MAX_VARIABLE_EXPENSES_PER_MONTH,
        );
        for ($index = 0; $index < $transactionsToCreate; ++$index) {
            /** @var array{category: string, title: string, minAmount: int, maxAmount: int} $template */
            $template = $this->randomItem($templates, $randomizer);
            $transactionIds[] = $this->createTransaction(
                user: $user,
                wallet: $wallets[$this->randomExpenseWalletKey($randomizer)],
                category: $this->getRequiredCategory($categories, $template['category']),
                type: TransactionType::EXPENSE,
                amount: $randomizer->getInt(
                    $template['minAmount'],
                    $template['maxAmount'],
                ),
                title: $template['title'],
                transactionDate: $this->randomDateInMonth(
                    month: $month,
                    randomizer: $randomizer,
                ),
            );
        }

        return $transactionIds;
    }

    /**
     * @param array<string, Category> $categories
     */
    private function getRequiredCategory(
        array $categories,
        string $name,
    ): Category {
        return $categories[$name]
            ?? throw new \LogicException(sprintf('Demo category "%s" not found.', $name));
    }

    private function randomExpenseWalletKey(Randomizer $randomizer): string
    {
        /** @var int $value */
        $value = $randomizer->getInt(1, 100);

        return match (true) {
            $value <= 75 => 'main',
            $value <= 90 => 'cash',
            default => 'card',
        };
    }

    private function randomDateInMonth(
        \DateTimeImmutable $month,
        Randomizer $randomizer,
        ?int $preferredDay = null,
    ): \DateTimeImmutable {
        /** @var \DateTimeImmutable $today */
        $today = new \DateTimeImmutable('today');
        /** @var int $lastAllowedDay */
        $lastAllowedDay = (int) $month->format('t');
        if ($month->format('Y-m') === $today->format('Y-m')) {
            $lastAllowedDay = min($lastAllowedDay, (int) $today->format('j'));
        }
        /** @var int $day */
        $day = null === $preferredDay
            ? $randomizer->getInt(1, $lastAllowedDay)
            : min($preferredDay, $lastAllowedDay);

        return $month
            ->setDate(
                (int) $month->format('Y'),
                (int) $month->format('m'),
                $day,
            )
            ->setTime(
                $randomizer->getInt(7, 22),
                $randomizer->getInt(0, 59),
            );
    }

    /**
     * @template T
     *
     * @param non-empty-list<T> $items
     *
     * @return T
     */
    private function randomItem(
        array $items,
        Randomizer $randomizer,
    ): mixed {
        return $items[$randomizer->getInt(0, count($items) - 1)];
    }

    private function createTransaction(
        User $user,
        Wallet $wallet,
        Category $category,
        TransactionType $type,
        int $amount,
        string $title,
        \DateTimeImmutable $transactionDate,
    ): int {
        /** @var int $walletId */
        $walletId = $wallet->getId()
            ?? throw new \LogicException('Demo wallet ID is required.');
        /** @var int $categoryId */
        $categoryId = $category->getId()
            ?? throw new \LogicException('Demo category ID is required.');

        return $this->createTransactionAction->execute(
            request: new CreateTransactionRequest(
                walletId: $walletId,
                categoryId: $categoryId,
                type: $type,
                amount: $amount,
                title: $title,
                transactionDate: $transactionDate,
            ),
            user: $user,
        )->id;
    }
}
