import axios from 'axios';

import { env } from '../config/env';
import { normalizeApiError } from './errors';

export const httpClient = axios.create({
  baseURL: env.apiUrl,
  timeout: env.apiTimeoutMs,
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
});

httpClient.interceptors.response.use(
  (response) => response,
  (error: unknown) => {
    return Promise.reject(normalizeApiError(error));
  },
);