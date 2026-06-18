import { httpClient } from '../../../shared/api';

export const deleteWallet = async (id: number): Promise<void> => {
  await httpClient.delete(`/wallets/${id}`);
};
