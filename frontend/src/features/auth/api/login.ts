import { httpClient } from '../../../shared/api';
import { mapAuthResponse } from '../adapters/mapAuthResponse';
import type {
  AuthResponse,
  LoginRequest,
} from '../types/auth.types';
import { AUTH_ENDPOINTS } from './authEndpoints';

export const login = async (
  request: LoginRequest,
): Promise<AuthResponse> => {
  const response = await httpClient.post<unknown>(
    AUTH_ENDPOINTS.login,
    request,
  );

  return mapAuthResponse(response.data);
};