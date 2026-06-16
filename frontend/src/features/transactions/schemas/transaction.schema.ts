import { z } from 'zod';

export const transactionTypeSchema = z.enum([
  'income',
  'expense',
]);

export const transactionSchema = z.object({
  id: z.number().int().positive(),
  walletId: z.number().int().positive(),
  walletName: z.string(),
  categoryId: z.number().int().positive(),
  categoryName: z.string(),
  type: transactionTypeSchema,
  amount: z.number().int(),
  currency: z.string().length(3),
  title: z.string(),
  description: z.string().nullable(),
  transactionDate: z.string(),
  createdAt: z.string(),
  updatedAt: z.string(),
});

const paginationSchema = z.object({
  page: z.number().int().positive(),
  limit: z.number().int().positive(),
  totalItems: z.number().int().nonnegative(),
  totalPages: z.number().int().nonnegative(),
});

export const transactionListSchema = z.object({
  items: z.array(transactionSchema),
  pagination: paginationSchema,
});
