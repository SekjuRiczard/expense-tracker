import { httpClient } from '../../../shared/api';
import { mapCurrentUserResponse } from '../adapters/mapAuthResponse';
import type {
  AuthUser,
  SessionStatus,
} from '../types/auth.types';
import { AUTH_ENDPOINTS } from './authEndpoints';

export interface CurrentUserResponse {
  readonly status: SessionStatus;
  readonly user: AuthUser;
}

export const getCurrentUser =
  async (): Promise<CurrentUserResponse> => {
    const response = await httpClient.get<unknown>(
      AUTH_ENDPOINTS.currentUser,
    );

    return mapCurrentUserResponse(response.data);
  };