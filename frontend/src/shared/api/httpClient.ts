import axios, {
  type AxiosError,
  type InternalAxiosRequestConfig,
} from 'axios';

import { env } from '../config/env';
import { normalizeApiError } from './errors';
import { notifyUnauthorized } from './sessionBridge';

type RetryableRequestConfig = InternalAxiosRequestConfig & {
  _retry?: boolean;
};

/**
 * Endpoints that must never trigger an automatic token refresh, to avoid
 * infinite refresh/login loops. The auth bootstrap handles `/me` itself.
 */
const NON_REFRESHABLE_PATHS = [
  '/login',
  '/register',
  '/token/refresh',
  '/logout',
  '/me',
];

export const httpClient = axios.create({
  baseURL: env.apiUrl,
  timeout: env.apiTimeoutMs,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
});

let refreshPromise: Promise<void> | null = null;

const runRefresh = (): Promise<void> => {
  if (!refreshPromise) {
    refreshPromise = httpClient
      .post('/token/refresh')
      .then(() => undefined)
      .finally(() => {
        refreshPromise = null;
      });
  }

  return refreshPromise;
};

const isNonRefreshablePath = (url: string | undefined): boolean => {
  if (!url) {
    return false;
  }

  return NON_REFRESHABLE_PATHS.some((path) => url.includes(path));
};

httpClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const config = error.config as RetryableRequestConfig | undefined;
    const status = error.response?.status;

    if (
      status === 401 &&
      config &&
      !config._retry &&
      !isNonRefreshablePath(config.url)
    ) {
      config._retry = true;

      try {
        await runRefresh();
      } catch {
        notifyUnauthorized();

        return Promise.reject(normalizeApiError(error));
      }

      return httpClient(config);
    }

    return Promise.reject(normalizeApiError(error));
  },
);
