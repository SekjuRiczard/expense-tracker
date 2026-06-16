import { httpClient } from '../../../shared/api';

export const deleteTransaction = async (id: number): Promise<void> => {
  await httpClient.delete(`/transactions/${id}`);
};
