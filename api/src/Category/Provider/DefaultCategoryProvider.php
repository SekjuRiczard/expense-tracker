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

namespace App\Category\Provider;

use App\Category\Enum\CategoryType;

final readonly class DefaultCategoryProvider
{
    /**
     * @return list<array{name: string, type: CategoryType}>
     */
    public function getCategories(): array
    {
        return [
            ['name' => 'Jedzenie i chemia', 'type' => CategoryType::EXPENSE],
            ['name' => 'Opłaty i rachunki', 'type' => CategoryType::EXPENSE],
            ['name' => 'Transport', 'type' => CategoryType::EXPENSE],
            ['name' => 'Zdrowie i uroda', 'type' => CategoryType::EXPENSE],
            ['name' => 'Rozrywka', 'type' => CategoryType::EXPENSE],
            ['name' => 'Zakupy', 'type' => CategoryType::EXPENSE],
            ['name' => 'Edukacja i rozwój', 'type' => CategoryType::EXPENSE],
            ['name' => 'Usługi', 'type' => CategoryType::EXPENSE],
            ['name' => 'Oszczędności i inwestycje', 'type' => CategoryType::EXPENSE],
            ['name' => 'Inne / Nieprzewidziane wydatki', 'type' => CategoryType::EXPENSE],
            ['name' => 'Pensja', 'type' => CategoryType::INCOME],
            ['name' => 'Freelance', 'type' => CategoryType::INCOME],
            ['name' => 'Zwrot', 'type' => CategoryType::INCOME],
            ['name' => 'Sprzedaż', 'type' => CategoryType::INCOME],
            ['name' => 'Premia', 'type' => CategoryType::INCOME],
            ['name' => 'Inne przychody', 'type' => CategoryType::INCOME],
        ];
    }
}
