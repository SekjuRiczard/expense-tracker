import { useMutation, useQueryClient } from '@tanstack/react-query';
import { createBudget } from '../api';
import { invalidateBudgetRelatedQueries } from './invalidateBudgetRelatedQueries';

export interface UseCreateBudgetCallbacks {
  readonly onSuccess: () => void;
  readonly onError: (error: unknown) => void;
}

export const useCreateBudget = (callbacks: UseCreateBudgetCallbacks) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createBudget,
    onSuccess: () => {
      invalidateBudgetRelatedQueries(queryClient);
      callbacks.onSuccess();
    },
    onError: (error: unknown) => {
      callbacks.onError(error);
    },
  });
};
