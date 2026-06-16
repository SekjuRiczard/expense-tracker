import { useMutation, useQueryClient } from '@tanstack/react-query';
import { updateBudget } from '../api';
import type { UpdateBudgetPayload } from '../types';
import { invalidateBudgetRelatedQueries } from './invalidateBudgetRelatedQueries';

export interface UseUpdateBudgetCallbacks {
  readonly onSuccess: () => void;
  readonly onError: (error: unknown) => void;
}

interface UpdateBudgetVariables {
  readonly id: number;
  readonly payload: UpdateBudgetPayload;
}

export const useUpdateBudget = (callbacks: UseUpdateBudgetCallbacks) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, payload }: UpdateBudgetVariables) =>
      updateBudget(id, payload),
    onSuccess: () => {
      invalidateBudgetRelatedQueries(queryClient);
      callbacks.onSuccess();
    },
    onError: (error: unknown) => {
      callbacks.onError(error);
    },
  });
};
