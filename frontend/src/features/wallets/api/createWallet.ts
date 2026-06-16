import { httpClient } from '../../../shared/api';
import { walletSchema } from '../schemas';
import type { CreateWalletPayload, Wallet } from '../types';

export const createWallet = async (
  payload: CreateWalletPayload,
): Promise<Wallet> => {
  const response = await httpClient.post<unknown>('/wallets', payload);
  return walletSchema.parse(response.data);
};
