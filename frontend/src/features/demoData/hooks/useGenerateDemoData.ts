import { useMutation, useQueryClient } from '@tanstack/react-query';
import { isApiError } from '../../../shared/api';
import { generateDemoData } from '../api';

export interface UseGenerateDemoDataCallbacks {
  readonly onSuccess: () => void;
  readonly onDataExists: () => void;
  readonly onForbidden: () => void;
  readonly onError: () => void;
}

export const useGenerateDemoData = (
  callbacks: UseGenerateDemoDataCallbacks,
) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: generateDemoData,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['analytics'] });
      void queryClient.invalidateQueries({ queryKey: ['wallets'] });
      void queryClient.invalidateQueries({ queryKey: ['transactions'] });
      callbacks.onSuccess();
    },
    onError: (error: unknown) => {
      if (isApiError(error)) {
        if (error.status === 409) {
          callbacks.onDataExists();
          return;
        }
        if (error.status === 403) {
          callbacks.onForbidden();
          return;
        }
      }
      callbacks.onError();
    },
  });
};
