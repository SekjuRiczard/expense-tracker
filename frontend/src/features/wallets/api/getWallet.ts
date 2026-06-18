import { httpClient } from '../../../shared/api';
import { walletSchema } from '../schemas';
import type { Wallet } from '../types';

export const getWallet = async (id: number): Promise<Wallet> => {
  const response = await httpClient.get<unknown>(`/wallets/${id}`);
  return walletSchema.parse(response.data);
};
