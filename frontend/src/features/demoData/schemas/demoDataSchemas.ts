import { z } from 'zod';

export const generateDemoDataResponseSchema = z.object({
  seed: z.number().int(),
  monthsGenerated: z.number().int(),
  defaultCategoriesCreated: z.number().int(),
  walletsCreated: z.number().int(),
  budgetsCreated: z.number().int(),
  transactionsCreated: z.number().int(),
});

export const clearDemoDataResponseSchema = z.object({
  transactionsDeleted: z.number().int(),
  budgetsDeleted: z.number().int(),
  walletsDeleted: z.number().int(),
});
