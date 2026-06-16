import {
  z,
} from 'zod';

const moneyAmountSchema = z
  .number()
  .int();

const analyticsSummarySchema = z.object({
  currency: z.string().length(3),
  from: z.string(),
  to: z.string(),
  income: moneyAmountSchema,
  expense: moneyAmountSchema,
  balance: moneyAmountSchema,
  transactionCount: z.number().int().nonnegative(),
});

const categoryBreakdownItemSchema = z.object({
  categoryId: z.number().int().positive(),
  categoryName: z.string(),
  amount: moneyAmountSchema,
  percentage: z.number(),
});

const cashFlowPointSchema = z.object({
  period: z.string(),
  income: moneyAmountSchema,
  expense: moneyAmountSchema,
  balance: moneyAmountSchema,
});

export const analyticsDashboardSchema = z.object({
  summary: analyticsSummarySchema,
  categoryBreakdown: z.array(categoryBreakdownItemSchema),
  cashFlow: z.array(cashFlowPointSchema),
});
