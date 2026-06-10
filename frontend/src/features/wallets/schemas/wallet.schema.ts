import {
  z,
} from 'zod';

export const walletSchema = z.object({
  id: z.number().int().positive(),
  name: z.string(),
  type: z.enum([
    'cash',
    'bank_account',
    'credit_card',
    'savings_account',
  ]),
  currency: z.string().length(3),
  balanceAmount: z.number().int(),
  createdAt: z.string(),
  updatedAt: z.string(),
});

export const walletsSchema = z.array(walletSchema);
