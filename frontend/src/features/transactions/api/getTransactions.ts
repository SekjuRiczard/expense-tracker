import { httpClient } from '../../../shared/api';
import { transactionListSchema } from '../schemas';
import type { TransactionFilters, TransactionList } from '../types';

const buildQueryParams = (
  filters: TransactionFilters,
): Record<string, string | number> => {
  const params: Record<string, string | number> = {};

  for (const [key, value] of Object.entries(filters)) {
    if (value === undefined || value === null || value === '') {
      continue;
    }
    params[key] = value as string | number;
  }

  return params;
};

export const getTransactions = async (
  filters: TransactionFilters,
): Promise<TransactionList> => {
  const response = await httpClient.get<unknown>('/transactions', {
    params: buildQueryParams(filters),
  });

  return transactionListSchema.parse(response.data);
};
