import { useMutation, useQueryClient } from '@tanstack/react-query';
import { updateCategory } from '../api';
import type { UpdateCategoryPayload } from '../types';
import { invalidateCategoryRelatedQueries } from './invalidateCategoryRelatedQueries';

export interface UseUpdateCategoryCallbacks {
  readonly onSuccess: () => void;
  readonly onError: (error: unknown) => void;
}

export const useUpdateCategory = (callbacks: UseUpdateCategoryCallbacks) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      id,
      payload,
    }: {
      id: number;
      payload: UpdateCategoryPayload;
    }) => updateCategory(id, payload),
    onSuccess: (_data, variables) => {
      invalidateCategoryRelatedQueries(queryClient);
      void queryClient.invalidateQueries({
        queryKey: ['categories', variables.id],
      });
      callbacks.onSuccess();
    },
    onError: (error) => {
      callbacks.onError(error);
    },
  });
};
