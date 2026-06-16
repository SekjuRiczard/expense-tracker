import { httpClient } from '../../../shared/api';
import { budgetSchema } from '../schemas';
import type { Budget } from '../types';

export const getBudget = async (id: number): Promise<Budget> => {
  const response = await httpClient.get<unknown>(`/budgets/${id}`);

  return budgetSchema.parse(response.data);
};
