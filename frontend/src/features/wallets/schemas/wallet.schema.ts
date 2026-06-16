import { z } from 'zod';

export const walletTypeSchema = z.enum([
  'cash',
  'bank_account',
  'credit_card',
  'savings_account',
]);

export const currencyCodeSchema = z.enum([
  'PLN',
  'EUR',
  'USD',
  'GBP',
]);

export const walletSchema = z.object({
  id: z.number().int().positive(),
  name: z.string(),
  type: walletTypeSchema,
  currency: currencyCodeSchema,
  balanceAmount: z.number().int(),
  createdAt: z.string(),
  updatedAt: z.string(),
});

export const walletsSchema = z.array(walletSchema);
