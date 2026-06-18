import { httpClient } from '../../../shared/api';
import { mapAuthResponse } from '../adapters/mapAuthResponse';
import type { AuthResponse } from '../types/auth.types';
import { AUTH_ENDPOINTS } from './authEndpoints';

export const refreshSession =
  async (): Promise<AuthResponse> => {
    const response = await httpClient.post<unknown>(
      AUTH_ENDPOINTS.refreshSession,
    );

    return mapAuthResponse(response.data);
  };