import {
  authResponsePayloadSchema,
  currentUserResponsePayloadSchema,
} from '../schemas/authResponse.schema';
import type {
  AuthResponse,
  AuthUser,
  SessionStatus,
} from '../types/auth.types';

const mapAuthUser = (
  payload: {
    readonly id: string;
    readonly email: string;
    readonly username: string;
    readonly hasPin: boolean;
    readonly roles?: readonly string[];
  },
): AuthUser => {
  return {
    id: payload.id,
    email: payload.email,
    username: payload.username,
    hasPin: payload.hasPin,
    roles: payload.roles,
  };
};

export const mapAuthResponse = (
  payload: unknown,
): AuthResponse => {
  const parsedPayload = authResponsePayloadSchema.parse(payload);

  return {
    status: parsedPayload.status,
    message: parsedPayload.message,
    user: mapAuthUser(parsedPayload.user),
  };
};

export const mapCurrentUserResponse = (
  payload: unknown,
): {
  readonly status: SessionStatus;
  readonly user: AuthUser;
} => {
  const parsedPayload =
    currentUserResponsePayloadSchema.parse(payload);

  return {
    status: parsedPayload.status,
    user: mapAuthUser(parsedPayload.user),
  };
};