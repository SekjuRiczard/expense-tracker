import {
  httpClient,
} from '../../../shared/api';
import {
  walletsSchema,
} from '../schemas';
import type {
  Wallet,
} from '../types';

export const getWallets = async (): Promise<readonly Wallet[]> => {
  const response = await httpClient.get<unknown>(
    '/wallets',
  );

  return walletsSchema.parse(response.data);
};
