export interface ApiViolation {
  readonly propertyPath: string;
  readonly message: string;
}

export interface ApiErrorOptions {
  readonly status: number | null;
  readonly message: string;
  readonly violations?: ReadonlyArray<ApiViolation>;
  readonly cause?: unknown;
}