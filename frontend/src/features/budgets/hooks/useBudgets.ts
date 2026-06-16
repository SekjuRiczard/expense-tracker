import { useQuery } from '@tanstack/react-query';
import { getBudgets } from '../api';

export const useBudgets = () => {
  return useQuery({
    queryKey: ['budgets'],
    queryFn: getBudgets,
  });
};
