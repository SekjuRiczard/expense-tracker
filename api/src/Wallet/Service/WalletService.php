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

namespace App\Wallet\Service;

use App\Entity\User;
use App\Wallet\Dto\Request\CreateWalletRequest;
use App\Wallet\Dto\Request\UpdateWalletRequest;
use App\Wallet\Dto\Response\WalletResponse;
use App\Wallet\Entity\Wallet;
use App\Wallet\Exception\WalletException;
use App\Wallet\Repository\WalletRepository;

final readonly class WalletService
{
    public function __construct(private WalletRepository $walletRepository)
    {
    }

    public function createWallet(CreateWalletRequest $request, User $user): WalletResponse
    {
        /** @var Wallet $wallet */
        $wallet = new Wallet(
            user: $user,
            name: $request->name,
            type: $request->type,
            currency: $request->currency,
            balanceAmount: $request->balanceAmount,
        );
        $this->walletRepository->save($wallet);

        return WalletResponse::fromEntity($wallet);
    }

    /**
     * @return list<WalletResponse>
     */
    public function getWallets(User $user): array
    {
        return array_map(
            static fn (Wallet $wallet): WalletResponse => WalletResponse::fromEntity($wallet),
            $this->walletRepository->findByUser($user),
        );
    }

    public function getWallet(int $id, User $user): WalletResponse
    {
        return WalletResponse::fromEntity($this->getUserWallet($id, $user));
    }

    public function updateWallet(int $id, UpdateWalletRequest $request, User $user): WalletResponse
    {
        if (null === $request->name && null === $request->type) {
            throw WalletException::emptyUpdateRequest();
        }
        /** @var Wallet $wallet */
        $wallet = $this->getUserWallet($id, $user);
        $wallet->update(
            name: $request->name ?? $wallet->getName(),
            type: $request->type ?? $wallet->getType(),
        );
        $this->walletRepository->save($wallet);

        return WalletResponse::fromEntity($wallet);
    }

    public function deleteWallet(int $id, User $user): void
    {
        $this->walletRepository->remove($this->getUserWallet($id, $user));
    }

    private function getUserWallet(int $id, User $user): Wallet
    {
        return $this->walletRepository->findOneByIdAndUser($id, $user) ?? throw WalletException::notFound();
    }
}
