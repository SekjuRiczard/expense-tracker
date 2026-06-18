import { useMutation, useQueryClient } from '@tanstack/react-query';
import { createTransaction } from '../api';
import { invalidateTransactionRelatedQueries } from './invalidateTransactionRelatedQueries';

export interface UseCreateTransactionCallbacks {
  readonly onSuccess: () => void;
  readonly onError: (error: unknown) => void;
}

export const useCreateTransaction = (
  callbacks: UseCreateTransactionCallbacks,
) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createTransaction,
    onSuccess: () => {
      invalidateTransactionRelatedQueries(queryClient);
      callbacks.onSuccess();
    },
    onError: (error) => {
      callbacks.onError(error);
    },
  });
};
