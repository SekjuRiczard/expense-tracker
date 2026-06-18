import { httpClient } from '../../../shared/api';
import { AUTH_ENDPOINTS } from './authEndpoints';

export const logout = async (): Promise<void> => {
  await httpClient.post(AUTH_ENDPOINTS.logout);
};