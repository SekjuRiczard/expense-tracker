import type { QueryClient } from '@tanstack/react-query';

export const DEMO_DATA_STATUS_QUERY_KEY = ['demo-data', 'status'] as const;

export const invalidateDemoDataRelatedQueries = (
  queryClient: QueryClient,
): void => {
  void queryClient.invalidateQueries({ queryKey: DEMO_DATA_STATUS_QUERY_KEY });
  void queryClient.invalidateQueries({ queryKey: ['wallets'] });
  void queryClient.invalidateQueries({ queryKey: ['budgets'] });
  void queryClient.invalidateQueries({ queryKey: ['transactions'] });
  void queryClient.invalidateQueries({ queryKey: ['categories'] });
  void queryClient.invalidateQueries({ queryKey: ['analytics'] });
  void queryClient.invalidateQueries({ queryKey: ['dashboard'] });
};
