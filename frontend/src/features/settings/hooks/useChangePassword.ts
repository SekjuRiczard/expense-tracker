import { useMutation, useQueryClient } from '@tanstack/react-query';
import { changePassword } from '../api';
import type { ChangePasswordPayload } from '../api';

export const useChangePassword = (onSuccess: () => void) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (payload: ChangePasswordPayload) => changePassword(payload),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['settings'] });
      onSuccess();
    },
  });
};
