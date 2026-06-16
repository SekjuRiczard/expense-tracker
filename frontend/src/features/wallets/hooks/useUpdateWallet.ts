import { useMutation, useQueryClient } from '@tanstack/react-query';
import { updateWallet } from '../api';
import type { UpdateWalletPayload } from '../types';

export interface UseUpdateWalletCallbacks {
  readonly onSuccess: () => void;
  readonly onError: () => void;
}

export const useUpdateWallet = (callbacks: UseUpdateWalletCallbacks) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, payload }: { id: number; payload: UpdateWalletPayload }) =>
      updateWallet(id, payload),
    onSuccess: (_data, variables) => {
      void queryClient.invalidateQueries({ queryKey: ['wallets'] });
      void queryClient.invalidateQueries({ queryKey: ['wallets', variables.id] });
      callbacks.onSuccess();
    },
    onError: () => {
      callbacks.onError();
    },
  });
};
