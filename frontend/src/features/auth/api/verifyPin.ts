import { httpClient } from '../../../shared/api';
import { mapAuthResponse } from '../adapters/mapAuthResponse';
import type {
  AuthResponse,
  PinRequest,
} from '../types/auth.types';
import { AUTH_ENDPOINTS } from './authEndpoints';

export const verifyPin = async (
  request: PinRequest,
): Promise<AuthResponse> => {
  const response = await httpClient.post<unknown>(
    AUTH_ENDPOINTS.verifyPin,
    request,
  );

  return mapAuthResponse(response.data);
};