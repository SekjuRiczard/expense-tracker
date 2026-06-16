import { z } from 'zod';
import { categoryTypeSchema } from './category.schema';

export const categoryFormSchema = z.object({
  name: z
    .string()
    .min(1, 'Name is required')
    .max(50, 'Name must not exceed 50 characters'),
  type: categoryTypeSchema,
});

export type CategoryFormData = z.infer<typeof categoryFormSchema>;
