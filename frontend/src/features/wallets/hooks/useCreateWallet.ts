import { useMutation, useQueryClient } from '@tanstack/react-query';
import { createWallet } from '../api';

export interface UseCreateWalletCallbacks {
  readonly onSuccess: () => void;
  readonly onError: () => void;
}

export const useCreateWallet = (callbacks: UseCreateWalletCallbacks) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createWallet,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['wallets'] });
      callbacks.onSuccess();
    },
    onError: () => {
      callbacks.onError();
    },
  });
};
