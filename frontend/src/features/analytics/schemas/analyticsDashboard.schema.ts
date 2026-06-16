import { z } from 'zod';

const moneyAmountSchema = z.number().int();

export const analyticsCurrencySchema = z.enum([
  'PLN',
  'EUR',
  'USD',
]);

export const periodSummarySchema = z.object({
  currency: z.string().length(3),
  from: z.string(),
  to: z.string(),
  income: moneyAmountSchema,
  expense: moneyAmountSchema,
  balance: moneyAmountSchema,
  transactionCount: z.number().int().nonnegative(),
});

export const categoryBreakdownItemSchema = z.object({
  categoryId: z.number().int().positive(),
  categoryName: z.string(),
  amount: moneyAmountSchema,
  percentage: z.number(),
});

export const cashFlowPointSchema = z.object({
  period: z.string(),
  income: moneyAmountSchema,
  expense: moneyAmountSchema,
  balance: moneyAmountSchema,
});

export const analyticsDashboardSchema = z.object({
  summary: periodSummarySchema,
  categoryBreakdown: z.array(categoryBreakdownItemSchema),
  cashFlow: z.array(cashFlowPointSchema),
});
