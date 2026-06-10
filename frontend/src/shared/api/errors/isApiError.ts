import { ApiError } from './ApiError';

export const isApiError = (
  error: unknown,
): error is ApiError => {
  return error instanceof ApiError;
};