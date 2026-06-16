import { httpClient } from '../../../shared/api';

export const logoutDevice = async (sessionId: string): Promise<void> => {
  await httpClient.delete(`/auth/sessions/${sessionId}`);
};
