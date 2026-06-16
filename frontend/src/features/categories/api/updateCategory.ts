import { httpClient } from '../../../shared/api';
import { categorySchema } from '../schemas';
import type { Category, UpdateCategoryPayload } from '../types';

export const updateCategory = async (
  id: number,
  payload: UpdateCategoryPayload,
): Promise<Category> => {
  const response = await httpClient.patch<unknown>(
    `/categories/${id}`,
    payload,
  );
  return categorySchema.parse(response.data);
};
