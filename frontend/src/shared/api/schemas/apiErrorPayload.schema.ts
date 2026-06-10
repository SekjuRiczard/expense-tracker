import { z } from 'zod';

export const apiViolationPayloadSchema = z.object({
  propertyPath: z.string().optional(),
  field: z.string().optional(),
  message: z.string().optional(),
  title: z.string().optional(),
});

export const apiErrorPayloadSchema = z.object({
  message: z.string().optional(),
  detail: z.string().optional(),
  title: z.string().optional(),
  violations: z
    .array(apiViolationPayloadSchema)
    .optional()
    .default([]),
});

export type ApiViolationPayload = z.infer<
  typeof apiViolationPayloadSchema
>;

export type ApiErrorPayload = z.infer<
  typeof apiErrorPayloadSchema
>;