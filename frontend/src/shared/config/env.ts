import { z } from 'zod';

const DEFAULT_API_TIMEOUT_MS = 10_000;

const apiUrlSchema = z
    .string()
    .min(1)
    .default('/api')
    .transform((url) => url.replace(/\/+$/, ''))
    .refine((url) => {
      return url.startsWith('http://')
          || url.startsWith('https://')
          || url.startsWith('/');
    }, 'VITE_API_URL must be an absolute URL or a relative path like /api');

const envSchema = z.object({
  VITE_API_URL: apiUrlSchema,
  VITE_API_TIMEOUT_MS: z.coerce
      .number()
      .int()
      .positive()
      .default(DEFAULT_API_TIMEOUT_MS),
});

const parsedEnv = envSchema.safeParse(import.meta.env);

if (!parsedEnv.success) {
  const details = parsedEnv.error.issues
      .map((issue) => {
        return `${issue.path.join('.')}: ${issue.message}`;
      })
      .join('; ');

  throw new Error(
      `Invalid frontend environment configuration: ${details}`,
  );
}

export const env = {
  apiUrl: parsedEnv.data.VITE_API_URL,
  apiTimeoutMs: parsedEnv.data.VITE_API_TIMEOUT_MS,
} as const;