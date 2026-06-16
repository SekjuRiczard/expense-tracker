import { httpClient } from '../../../shared/api';
import { budgetSchema } from '../schemas';
import type { Budget, UpdateBudgetPayload } from '../types';

export const updateBudget = async (
  id: number,
  payload: UpdateBudgetPayload,
): Promise<Budget> => {
  const response = await httpClient.patch<unknown>(`/budgets/${id}`, payload);

  return budgetSchema.parse(response.data);
};
