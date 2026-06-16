import { httpClient } from '../../../shared/api';
import { budgetSchema } from '../schemas';
import type { Budget, CreateBudgetPayload } from '../types';

export const createBudget = async (
  payload: CreateBudgetPayload,
): Promise<Budget> => {
  const response = await httpClient.post<unknown>('/budgets', payload);

  return budgetSchema.parse(response.data);
};
