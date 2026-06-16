import { useMutation, useQueryClient } from '@tanstack/react-query';
import { logoutDevice } from '../api';

export const useLogoutDevice = (onSuccess: () => void) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (sessionId: string) => logoutDevice(sessionId),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['settings', 'sessions'] });
      onSuccess();
    },
  });
};
