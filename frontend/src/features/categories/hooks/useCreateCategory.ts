import { useMutation, useQueryClient } from '@tanstack/react-query';
import { createCategory } from '../api';
import { invalidateCategoryRelatedQueries } from './invalidateCategoryRelatedQueries';

export interface UseCreateCategoryCallbacks {
  readonly onSuccess: () => void;
  readonly onError: (error: unknown) => void;
}

export const useCreateCategory = (callbacks: UseCreateCategoryCallbacks) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createCategory,
    onSuccess: () => {
      invalidateCategoryRelatedQueries(queryClient);
      callbacks.onSuccess();
    },
    onError: (error) => {
      callbacks.onError(error);
    },
  });
};
