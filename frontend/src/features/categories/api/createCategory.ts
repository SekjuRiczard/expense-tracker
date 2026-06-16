import { httpClient } from '../../../shared/api';
import { categorySchema } from '../schemas';
import type { Category, CreateCategoryPayload } from '../types';

export const createCategory = async (
  payload: CreateCategoryPayload,
): Promise<Category> => {
  const response = await httpClient.post<unknown>('/categories', payload);
  return categorySchema.parse(response.data);
};
