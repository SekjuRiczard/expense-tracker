export type SessionStatus =
  | 'pin_setup_required'
  | 'pin_verification_required'
  | 'authenticated'
  | 'revoked'
  | 'expired';

export interface AuthUser {
  readonly id: string;
  readonly email: string;
  readonly username: string;
  readonly hasPin: boolean;
}

export interface AuthResponse {
  readonly status: SessionStatus;
  readonly message: string;
  readonly user: AuthUser;
}

export interface LoginRequest {
  readonly email: string;
  readonly password: string;
}

export interface RegisterRequest {
  readonly username: string;
  readonly email: string;
  readonly password: string;
}

export interface PinRequest {
  readonly pin: string;
}