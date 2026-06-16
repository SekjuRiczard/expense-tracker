import type {
  ApiErrorOptions,
  ApiViolation,
} from './apiError.types';

export class ApiError extends Error {
  public readonly status: number | null;

  public readonly violations: ReadonlyArray<ApiViolation>;

  public constructor({
    status,
    message,
    violations = [],
    cause,
  }: ApiErrorOptions) {
    super(message, { cause });

    this.name = 'ApiError';
    this.status = status;
    this.violations = Object.freeze([...violations]);
  }
}