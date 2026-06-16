import { httpClient } from '../../../shared/api';
import { walletSchema } from '../schemas';
import type { UpdateWalletPayload, Wallet } from '../types';

export const updateWallet = async (
  id: number,
  payload: UpdateWalletPayload,
): Promise<Wallet> => {
  const response = await httpClient.patch<unknown>(
    `/wallets/${id}`,
    payload,
  );
  return walletSchema.parse(response.data);
};
