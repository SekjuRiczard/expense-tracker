import type { QueryClient } from '@tanstack/react-query';

export const invalidateCategoryRelatedQueries = (
  queryClient: QueryClient,
): void => {
  void queryClient.invalidateQueries({ queryKey: ['categories'] });
  void queryClient.invalidateQueries({ queryKey: ['transactions'] });
  void queryClient.invalidateQueries({ queryKey: ['analytics'] });
};
