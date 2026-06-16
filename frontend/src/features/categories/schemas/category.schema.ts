import { z } from 'zod';

export const categoryTypeSchema = z.enum([
  'income',
  'expense',
]);

export const categorySchema = z.object({
  id: z.number().int().positive(),
  name: z.string(),
  type: categoryTypeSchema,
  isDefault: z.boolean(),
  createdAt: z.string(),
  updatedAt: z.string(),
});

export const categoriesSchema = z.array(categorySchema);
