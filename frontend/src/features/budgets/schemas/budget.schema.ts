import { z } from 'zod';

export const budgetPeriodTypeSchema = z.enum([
  'monthly',
  'yearly',
  'custom',
]);

export const currencyCodeSchema = z.enum([
  'PLN',
  'EUR',
  'USD',
  'GBP',
]);

export const budgetStatusSchema = z.enum([
  'ok',
  'warning',
  'exceeded',
]);

export const budgetSchema = z.object({
  id: z.number().int().positive(),
  name: z.string(),
  amount: z.number().int(),
  currency: currencyCodeSchema,
  periodType: budgetPeriodTypeSchema,
  startDate: z.string(),
  endDate: z.string(),
  createdAt: z.string(),
  updatedAt: z.string(),
});

export const budgetWithUsageSchema = budgetSchema.extend({
  spentAmount: z.number().int(),
  remainingAmount: z.number().int(),
  percentage: z.number(),
  status: budgetStatusSchema,
});

export const budgetsWithUsageSchema = z.array(budgetWithUsageSchema);
