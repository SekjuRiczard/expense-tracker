import type { QueryClient } from '@tanstack/react-query';

export const invalidateBudgetRelatedQueries = (
  queryClient: QueryClient,
): void => {
  void queryClient.invalidateQueries({ queryKey: ['budgets'] });
  void queryClient.invalidateQueries({ queryKey: ['analytics'] });
};
