import type { QueryClient } from '@tanstack/react-query';

export const invalidateTransactionRelatedQueries = (
  queryClient: QueryClient,
): void => {
  void queryClient.invalidateQueries({ queryKey: ['transactions'] });
  void queryClient.invalidateQueries({ queryKey: ['analytics'] });
  void queryClient.invalidateQueries({ queryKey: ['wallets'] });
  void queryClient.invalidateQueries({ queryKey: ['budgets'] });
};
