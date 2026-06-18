import { useMutation, useQueryClient } from '@tanstack/react-query';
import { deleteWallet } from '../api';

export interface UseDeleteWalletCallbacks {
  readonly onSuccess: () => void;
  readonly onError: () => void;
}

export const useDeleteWallet = (callbacks: UseDeleteWalletCallbacks) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => deleteWallet(id),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['wallets'] });
      callbacks.onSuccess();
    },
    onError: () => {
      callbacks.onError();
    },
  });
};
