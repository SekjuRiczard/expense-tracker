import { httpClient } from '../../../shared/api';
import { transactionSchema } from '../schemas';
import type { Transaction } from '../types';

export const getTransaction = async (id: number): Promise<Transaction> => {
  const response = await httpClient.get<unknown>(`/transactions/${id}`);
  return transactionSchema.parse(response.data);
};
