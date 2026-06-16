import { httpClient } from '../../../shared/api';
import { categoriesSchema } from '../schemas';
import type { Category } from '../types';

export const getCategories = async (): Promise<readonly Category[]> => {
  const response = await httpClient.get<unknown>('/categories');
  return categoriesSchema.parse(response.data);
};
