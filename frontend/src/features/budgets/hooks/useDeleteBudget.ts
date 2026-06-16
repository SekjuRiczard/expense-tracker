import { useMutation, useQueryClient } from '@tanstack/react-query';
import { deleteBudget } from '../api';
import { invalidateBudgetRelatedQueries } from './invalidateBudgetRelatedQueries';

export interface UseDeleteBudgetCallbacks {
  readonly onSuccess: () => void;
  readonly onError: (error: unknown) => void;
}

export const useDeleteBudget = (callbacks: UseDeleteBudgetCallbacks) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => deleteBudget(id),
    onSuccess: () => {
      invalidateBudgetRelatedQueries(queryClient);
      callbacks.onSuccess();
    },
    onError: (error: unknown) => {
      callbacks.onError(error);
    },
  });
};
