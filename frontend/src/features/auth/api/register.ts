import { httpClient } from '../../../shared/api';
import { mapAuthResponse } from '../adapters/mapAuthResponse';
import type {
  AuthResponse,
  RegisterRequest,
} from '../types/auth.types';
import { AUTH_ENDPOINTS } from './authEndpoints';

export const register = async (
  request: RegisterRequest,
): Promise<AuthResponse> => {
  const response = await httpClient.post<unknown>(
    AUTH_ENDPOINTS.register,
    request,
  );

  return mapAuthResponse(response.data);
};