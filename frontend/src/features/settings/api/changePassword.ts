import { httpClient } from '../../../shared/api';

export interface ChangePasswordPayload {
  readonly oldPassword: string;
  readonly newPassword: string;
  readonly confirmNewPassword: string;
}

export const changePassword = async (
  payload: ChangePasswordPayload,
): Promise<void> => {
  await httpClient.patch('/password/change', payload);
};
