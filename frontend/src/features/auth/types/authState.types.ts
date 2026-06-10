import type { ApiError } from '../../../shared/api';
import type { AuthUser } from './auth.types';

export type AuthState =
  | {
      readonly status: 'initializing';
    }
  | {
      readonly status: 'unauthenticated';
    }
  | {
      readonly status: 'pin_setup_required';
      readonly user: AuthUser;
    }
  | {
      readonly status: 'pin_verification_required';
      readonly user: AuthUser;
    }
  | {
      readonly status: 'authenticated';
      readonly user: AuthUser;
    }
  | {
      readonly status: 'error';
      readonly error: ApiError;
    };