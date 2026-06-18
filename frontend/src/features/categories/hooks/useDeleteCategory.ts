import { useMutation, useQueryClient } from '@tanstack/react-query';
import { deleteCategory } from '../api';
import { invalidateCategoryRelatedQueries } from './invalidateCategoryRelatedQueries';

export interface UseDeleteCategoryCallbacks {
  readonly onSuccess: () => void;
  readonly onError: (error: unknown) => void;
}

export const useDeleteCategory = (callbacks: UseDeleteCategoryCallbacks) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => deleteCategory(id),
    onSuccess: () => {
      invalidateCategoryRelatedQueries(queryClient);
      callbacks.onSuccess();
    },
    onError: (error) => {
      callbacks.onError(error);
    },
  });
};
