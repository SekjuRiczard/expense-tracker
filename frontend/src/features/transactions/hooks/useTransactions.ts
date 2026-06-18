import { keepPreviousData, useQuery } from '@tanstack/react-query';
import { getTransactions } from '../api';
import type { TransactionFilters } from '../types';

export const useTransactions = (filters: TransactionFilters) => {
  return useQuery({
    queryKey: ['transactions', filters],
    queryFn: () => getTransactions(filters),
    placeholderData: keepPreviousData,
  });
};
