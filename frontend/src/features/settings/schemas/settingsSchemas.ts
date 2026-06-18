import { z } from 'zod';

export const settingsSessionSchema = z.object({
  id: z.string(),
  ipAddress: z.string().nullable(),
  userAgent: z.string().nullable(),
  createdAt: z.string(),
  expiresAt: z.string(),
});

export const settingsSessionsSchema = z.array(settingsSessionSchema);

export const changePasswordSchema = z.object({
  oldPassword: z.string().min(1, 'Current password is required'),
  newPassword: z.string().min(8, 'Minimum 8 characters'),
  confirmNewPassword: z.string().min(1, 'Please confirm your password'),
}).refine(
  (d) => d.newPassword === d.confirmNewPassword,
  { message: 'Passwords do not match', path: ['confirmNewPassword'] },
);

export const changePinSchema = z.object({
  oldPin: z.string().min(1, 'Current PIN is required'),
  newPin: z
    .string()
    .regex(/^\d{6}$/, 'PIN must be exactly 6 digits'),
  confirmPin: z.string().min(1, 'Please confirm your PIN'),
}).refine(
  (d) => d.newPin === d.confirmPin,
  { message: 'PINs do not match', path: ['confirmPin'] },
);

export const setupPinSchema = z.object({
  newPin: z
    .string()
    .regex(/^\d{6}$/, 'PIN must be exactly 6 digits'),
  confirmPin: z.string().min(1, 'Please confirm your PIN'),
}).refine(
  (d) => d.newPin === d.confirmPin,
  { message: 'PINs do not match', path: ['confirmPin'] },
);
