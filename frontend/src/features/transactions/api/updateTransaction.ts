import { httpClient } from '../../../shared/api';
import { transactionSchema } from '../schemas';
import type { Transaction, UpdateTransactionPayload } from '../types';

export const updateTransaction = async (
  id: number,
  payload: UpdateTransactionPayload,
): Promise<Transaction> => {
  const response = await httpClient.patch<unknown>(
    `/transactions/${id}`,
    payload,
  );
  return transactionSchema.parse(response.data);
};
