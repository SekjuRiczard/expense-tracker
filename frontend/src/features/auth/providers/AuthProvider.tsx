import {
  useCallback,
  useMemo,
  type ReactNode,
} from 'react';
import {
  useQuery,
  useQueryClient,
} from '@tanstack/react-query';

import {
  isApiError,
  normalizeApiError,
} from '../../../shared/api';
import {
  getCurrentUser,
  login as loginRequest,
  logout as logoutRequest,
  refreshSession as refreshSessionRequest,
  register as registerRequest,
  setupPin as setupPinRequest,
  verifyPin as verifyPinRequest,
  type CurrentUserResponse,
} from '../api';
import {
  mapSessionToAuthState,
} from '../adapters/mapSessionToAuthState';
import {
  AuthContext,
  type AuthContextValue,
} from '../context/AuthContext';
import type {
  AuthResponse,
  LoginRequest,
  PinRequest,
  RegisterRequest,
} from '../types/auth.types';
import type {
  AuthState,
} from '../types/authState.types';

const AUTH_SESSION_QUERY_KEY = [
  'auth',
  'session',
] as const;

const resolveInitialSession =
  async (): Promise<CurrentUserResponse | null> => {
    try {
      return await getCurrentUser();
    } catch (error: unknown) {
      if (!isApiError(error) || error.status !== 401) {
        throw error;
      }
    }

    try {
      return await refreshSessionRequest();
    } catch (error: unknown) {
      if (isApiError(error) && error.status === 401) {
        return null;
      }

      throw error;
    }
  };

export interface AuthProviderProps {
  readonly children: ReactNode;
}

export const AuthProvider = ({
  children,
}: AuthProviderProps) => {
  const queryClient = useQueryClient();

  const {
    data,
    error,
    isError,
    isPending,
    refetch,
  } = useQuery({
    queryKey: AUTH_SESSION_QUERY_KEY,
    queryFn: resolveInitialSession,
    retry: false,
    staleTime: Number.POSITIVE_INFINITY,
  });

  const setSession = useCallback(
    (
      session: CurrentUserResponse | null,
    ): void => {
      queryClient.setQueryData(
        AUTH_SESSION_QUERY_KEY,
        session,
      );
    },
    [queryClient],
  );

  const state = useMemo<AuthState>(() => {
    if (isPending) {
      return {
        status: 'initializing',
      };
    }

    if (isError) {
      return {
        status: 'error',
        error: normalizeApiError(error),
      };
    }

    if (!data) {
      return {
        status: 'unauthenticated',
      };
    }

    return mapSessionToAuthState(
      data.status,
      data.user,
    );
  }, [
    data,
    error,
    isError,
    isPending,
  ]);

  const initializeSession = useCallback(
    async (): Promise<void> => {
      await refetch();
    },
    [refetch],
  );

  const login = useCallback(
    async (
      request: LoginRequest,
    ): Promise<AuthResponse> => {
      const response = await loginRequest(request);

      setSession(response);

      return response;
    },
    [setSession],
  );

  const register = useCallback(
    async (
      request: RegisterRequest,
    ): Promise<AuthResponse> => {
      const response = await registerRequest(request);

      setSession(response);

      return response;
    },
    [setSession],
  );

  const setupPin = useCallback(
    async (
      request: PinRequest,
    ): Promise<AuthResponse> => {
      const response = await setupPinRequest(request);

      setSession(response);

      return response;
    },
    [setSession],
  );

  const verifyPin = useCallback(
    async (
      request: PinRequest,
    ): Promise<AuthResponse> => {
      const response = await verifyPinRequest(request);

      setSession(response);

      return response;
    },
    [setSession],
  );

  const refreshSession = useCallback(
    async (): Promise<AuthResponse> => {
      const response =
        await refreshSessionRequest();

      setSession(response);

      return response;
    },
    [setSession],
  );

  const logout = useCallback(
    async (): Promise<void> => {
      await logoutRequest();

      setSession(null);
    },
    [setSession],
  );

  const value = useMemo<AuthContextValue>(() => {
    return {
      state,
      initializeSession,
      login,
      register,
      setupPin,
      verifyPin,
      refreshSession,
      logout,
    };
  }, [
    state,
    initializeSession,
    login,
    register,
    setupPin,
    verifyPin,
    refreshSession,
    logout,
  ]);

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};