import type {
  AuthState,
} from '../types/authState.types';
import type {
  AuthUser,
  SessionStatus,
} from '../types/auth.types';

export const mapSessionToAuthState = (
  status: SessionStatus,
  user: AuthUser,
): AuthState => {
  switch (status) {
    case 'pin_setup_required':
      return {
        status,
        user,
      };

    case 'pin_verification_required':
      return {
        status,
        user,
      };

    case 'authenticated':
      return {
        status,
        user,
      };

    case 'revoked':
    case 'expired':
      return {
        status: 'unauthenticated',
      };
  }
};