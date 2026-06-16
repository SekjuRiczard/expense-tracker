import { useMutation, useQueryClient } from '@tanstack/react-query';
import { changePinOrSetup } from '../api';

interface ChangePinMutationVars {
  readonly hasPin: boolean;
  readonly oldPin?: string;
  readonly newPin: string;
}

export const useChangePin = (onSuccess: () => void) => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (vars: ChangePinMutationVars) =>
      changePinOrSetup(vars),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['settings'] });
      void queryClient.invalidateQueries({ queryKey: ['auth', 'session'] });
      onSuccess();
    },
  });
};
