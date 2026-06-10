import {
  QueryClient,
} from '@tanstack/react-query';

import {
  isApiError,
} from '../../shared/api';

const MAX_QUERY_RETRY_COUNT = 1;

const shouldRetryQuery = (
  failureCount: number,
  error: Error,
): boolean => {
  if (failureCount >= MAX_QUERY_RETRY_COUNT) {
    return false;
  }

  if (!isApiError(error)) {
    return false;
  }

  if (error.status === null) {
    return true;
  }

  return error.status >= 500;
};

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: shouldRetryQuery,
      refetchOnWindowFocus: false,
    },
    mutations: {
      retry: false,
    },
  },
});