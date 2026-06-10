import axios from 'axios';

import {
  apiErrorPayloadSchema,
  type ApiErrorPayload,
  type ApiViolationPayload,
} from '../schemas/apiErrorPayload.schema';
import { ApiError } from './ApiError';
import type { ApiViolation } from './apiError.types';
import { isApiError } from './isApiError';

const DEFAULT_SERVER_ERROR_MESSAGE =
  'Wystąpił błąd serwera. Spróbuj ponownie później.';

const FALLBACK_MESSAGE_BY_STATUS = {
  400: 'The submitted data is invalid.',
  401: 'Session expired. Please log in again.',
  403: 'You do not have permission to perform this operation.',
  404: 'The requested resource was not found.',
  409: 'The operation cannot be performed due to a data conflict.',
  422: 'Please correct the form errors.',
} as const satisfies Readonly<Record<number, string>>;

const getFallbackMessage = (status: number): string => {
  return FALLBACK_MESSAGE_BY_STATUS[
    status as keyof typeof FALLBACK_MESSAGE_BY_STATUS
  ] ?? DEFAULT_SERVER_ERROR_MESSAGE;
};

const mapApiViolation = (
  payload: ApiViolationPayload,
): ApiViolation | null => {
  const message = payload.message ?? payload.title;

  if (!message) {
    return null;
  }

  return {
    propertyPath: payload.propertyPath ?? payload.field ?? '',
    message,
  };
};

const mapApiViolations = (
  payloads: ReadonlyArray<ApiViolationPayload>,
): ReadonlyArray<ApiViolation> => {
  return payloads
    .map(mapApiViolation)
    .filter(
      (violation): violation is ApiViolation => violation !== null,
    );
};

const getPayloadMessage = (
  payload: ApiErrorPayload,
  status: number,
): string => {
  return (
    payload.message
    ?? payload.detail
    ?? payload.title
    ?? getFallbackMessage(status)
  );
};

export const normalizeApiError = (
  error: unknown,
): ApiError => {
  if (isApiError(error)) {
    return error;
  }

  if (!axios.isAxiosError<unknown>(error)) {
    return new ApiError({
      status: null,
      message: 'Wystąpił nieoczekiwany błąd.',
      cause: error,
    });
  }

  if (!error.response) {
    return new ApiError({
      status: null,
      message: 'Nie udało się połączyć z serwerem.',
      cause: error,
    });
  }

  const status = error.response.status;

  const parsedPayload = apiErrorPayloadSchema.safeParse(
    error.response.data,
  );

  if (!parsedPayload.success) {
    return new ApiError({
      status,
      message: getFallbackMessage(status),
      cause: error,
    });
  }

  return new ApiError({
    status,
    message: getPayloadMessage(parsedPayload.data, status),
    violations: mapApiViolations(
      parsedPayload.data.violations,
    ),
    cause: error,
  });
};