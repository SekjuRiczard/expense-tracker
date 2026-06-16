import { z } from 'zod';
import { transactionTypeSchema } from './transaction.schema';

export const transactionFormSchema = z.object({
  type: transactionTypeSchema,
  walletId: z
    .number({ error: 'Wallet is required' })
    .int()
    .positive('Wallet is required'),
  categoryId: z
    .number({ error: 'Category is required' })
    .int()
    .positive('Category is required'),
  amount: z
    .number({ error: 'Amount must be a number' })
    .positive('Amount must be greater than 0'),
  title: z
    .string()
    .min(1, 'Title is required')
    .max(255, 'Title must not exceed 255 characters'),
  description: z
    .string()
    .max(1000, 'Description must not exceed 1000 characters'),
  transactionDate: z
    .string()
    .min(1, 'Transaction date is required'),
});

export type TransactionFormData = z.infer<typeof transactionFormSchema>;
