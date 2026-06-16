import { useMutation, useQueryClient } from '@tanstack/react-query';
import { isApiError } from '../../../shared/api';
import { clearDemoData } from '../api';
import { invalidateDemoDataRelatedQueries } from './invalidateDemoDataRelatedQueries';

export interface UseClearDemoDataCallbacks {
  readonly onSuccess: () => void;
  readonly onForbidden: () => void;
  readonly onError: () => void;
}

export const useClearDemoData = (
  callbacks: UseClearDemoDataCallbacks,
) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: clearDemoData,
    onSuccess: () => {
      invalidateDemoDataRelatedQueries(queryClient);
      callbacks.onSuccess();
    },
    onError: (error: unknown) => {
      if (isApiError(error)) {
        if (error.status === 403) {
          callbacks.onForbidden();
          return;
        }
      }
      callbacks.onError();
    },
  });
};
