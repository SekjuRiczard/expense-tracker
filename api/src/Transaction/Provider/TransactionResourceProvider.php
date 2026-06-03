<?php

/*
 * This file is part of the Expense Tracker.
 *
 * (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Transaction\Provider;

use App\Category\Entity\Category;
use App\Category\Repository\CategoryRepository;
use App\Entity\User;
use App\Transaction\Entity\Transaction;
use App\Transaction\Exception\TransactionException;
use App\Transaction\Repository\TransactionRepository;
use App\Wallet\Entity\Wallet;
use App\Wallet\Repository\WalletRepository;

final readonly class TransactionResourceProvider
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private WalletRepository $walletRepository,
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function getTransaction(int $id, User $user): Transaction
    {
        return $this->transactionRepository->findSingleByUser($id, $user)
            ?? throw TransactionException::notFound();
    }

    public function getWallet(int $id, User $user): Wallet
    {
        return $this->walletRepository->findOneByIdAndUser($id, $user)
            ?? throw TransactionException::walletNotFound();
    }

    public function getCategory(int $id, User $user): Category
    {
        return $this->categoryRepository->findSingleCategory($id, $user)
            ?? throw TransactionException::categoryNotFound();
    }
}
