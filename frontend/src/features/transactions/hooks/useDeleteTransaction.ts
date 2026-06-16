import { useMutation, useQueryClient } from '@tanstack/react-query';
import { deleteTransaction } from '../api';
import { invalidateTransactionRelatedQueries } from './invalidateTransactionRelatedQueries';

export interface UseDeleteTransactionCallbacks {
  readonly onSuccess: () => void;
  readonly onError: (error: unknown) => void;
}

export const useDeleteTransaction = (
  callbacks: UseDeleteTransactionCallbacks,
) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => deleteTransaction(id),
    onSuccess: () => {
      invalidateTransactionRelatedQueries(queryClient);
      callbacks.onSuccess();
    },
    onError: (error) => {
      callbacks.onError(error);
    },
  });
};
