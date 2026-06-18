import { useQuery } from '@tanstack/react-query';
import { getBudget } from '../api';

export const useBudget = (id: number | null) => {
  return useQuery({
    queryKey: ['budgets', id],
    queryFn: () => getBudget(id as number),
    enabled: id !== null,
  });
};
