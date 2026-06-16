import { z } from 'zod';
import { walletTypeSchema, currencyCodeSchema } from './wallet.schema';

export const createWalletFormSchema = z.object({
  name: z
    .string()
    .min(1, 'Name is required')
    .max(255, 'Name must not exceed 255 characters'),
  type: walletTypeSchema,
  currency: currencyCodeSchema,
  balance: z
    .number({ error: 'Balance must be a number' })
    .min(0, 'Balance must be at least 0'),
});

export const updateWalletFormSchema = z.object({
  name: z
    .string()
    .min(1, 'Name is required')
    .max(255, 'Name must not exceed 255 characters'),
  type: walletTypeSchema,
});

export type CreateWalletFormData = z.infer<typeof createWalletFormSchema>;
export type UpdateWalletFormData = z.infer<typeof updateWalletFormSchema>;
