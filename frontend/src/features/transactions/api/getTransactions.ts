import {
  httpClient,
} from '../../../shared/api';
import {
  transactionListSchema,
} from '../schemas';
import type {
  TransactionFilters,
  TransactionList,
} from '../types';

export const getTransactions = async (
  params: TransactionFilters,
): Promise<TransactionList> => {
  const response = await httpClient.get<unknown>(
    '/transactions',
    {
      params,
    },
  );

  return transactionListSchema.parse(response.data);
};
