import { httpClient } from '../../../shared/api';
import { transactionSchema } from '../schemas';
import type { CreateTransactionPayload, Transaction } from '../types';

export const createTransaction = async (
  payload: CreateTransactionPayload,
): Promise<Transaction> => {
  const response = await httpClient.post<unknown>('/transactions', payload);
  return transactionSchema.parse(response.data);
};
