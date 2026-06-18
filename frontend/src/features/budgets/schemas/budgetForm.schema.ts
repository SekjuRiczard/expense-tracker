import { z } from 'zod';
import { budgetPeriodTypeSchema, currencyCodeSchema } from './budget.schema';

export const budgetFormSchema = z
  .object({
    name: z
      .string()
      .min(1, 'Name is required')
      .max(100, 'Name must not exceed 100 characters'),
    amount: z
      .number({ error: 'Amount must be a number' })
      .positive('Amount must be greater than 0'),
    currency: currencyCodeSchema,
    periodType: budgetPeriodTypeSchema,
    startDate: z.string().min(1, 'Start date is required'),
    endDate: z.string().min(1, 'End date is required'),
  })
  .refine(
    (data) => data.endDate >= data.startDate,
    {
      message: 'End date must not be before the start date',
      path: ['endDate'],
    },
  );

export type BudgetFormData = z.infer<typeof budgetFormSchema>;
