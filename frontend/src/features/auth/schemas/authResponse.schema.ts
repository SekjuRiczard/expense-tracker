import { z } from 'zod';

export const sessionStatusSchema = z.enum([
  'pin_setup_required',
  'pin_verification_required',
  'authenticated',
  'revoked',
  'expired',
]);

export const authUserPayloadSchema = z.object({
  id: z.string(),
  email: z.string().email(),
  username: z.string(),
  hasPin: z.boolean(),
  roles: z.array(z.string()).optional(),
});

export const authResponsePayloadSchema = z.object({
  status: sessionStatusSchema,
  message: z.string(),
  user: authUserPayloadSchema,
});

export const currentUserResponsePayloadSchema = z.object({
  status: sessionStatusSchema,
  user: authUserPayloadSchema,
});

export type AuthResponsePayload = z.infer<
  typeof authResponsePayloadSchema
>;

export type CurrentUserResponsePayload = z.infer<
  typeof currentUserResponsePayloadSchema
>;