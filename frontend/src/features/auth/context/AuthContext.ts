import { createContext } from 'react';

import type {
  AuthResponse,
  LoginRequest,
  PinRequest,
  RegisterRequest,
} from '../types/auth.types';
import type {
  AuthState,
} from '../types/authState.types';

export interface AuthContextValue {
  readonly state: AuthState;

  readonly initializeSession: () => Promise<void>;

  readonly login: (
    request: LoginRequest,
  ) => Promise<AuthResponse>;

  readonly register: (
    request: RegisterRequest,
  ) => Promise<AuthResponse>;

  readonly setupPin: (
    request: PinRequest,
  ) => Promise<AuthResponse>;

  readonly verifyPin: (
    request: PinRequest,
  ) => Promise<AuthResponse>;

  readonly refreshSession: () => Promise<AuthResponse>;

  readonly logout: () => Promise<void>;
}

export const AuthContext = createContext<
  AuthContextValue | undefined
>(undefined);