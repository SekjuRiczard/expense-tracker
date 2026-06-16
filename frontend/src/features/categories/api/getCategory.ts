import { httpClient } from '../../../shared/api';
import { categorySchema } from '../schemas';
import type { Category } from '../types';

export const getCategory = async (id: number): Promise<Category> => {
  const response = await httpClient.get<unknown>(`/categories/${id}`);
  return categorySchema.parse(response.data);
};
