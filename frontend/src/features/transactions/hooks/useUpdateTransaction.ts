import { useMutation, useQueryClient } from '@tanstack/react-query';
import { updateTransaction } from '../api';
import type { UpdateTransactionPayload } from '../types';
import { invalidateTransactionRelatedQueries } from './invalidateTransactionRelatedQueries';

export interface UseUpdateTransactionCallbacks {
  readonly onSuccess: () => void;
  readonly onError: (error: unknown) => void;
}

export const useUpdateTransaction = (
  callbacks: UseUpdateTransactionCallbacks,
) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({
      id,
      payload,
    }: {
      id: number;
      payload: UpdateTransactionPayload;
    }) => updateTransaction(id, payload),
    onSuccess: (_data, variables) => {
      invalidateTransactionRelatedQueries(queryClient);
      void queryClient.invalidateQueries({
        queryKey: ['transactions', variables.id],
      });
      callbacks.onSuccess();
    },
    onError: (error) => {
      callbacks.onError(error);
    },
  });
};
