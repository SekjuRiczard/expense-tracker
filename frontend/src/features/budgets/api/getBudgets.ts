import { httpClient } from '../../../shared/api';
import { budgetsWithUsageSchema } from '../schemas';
import type { BudgetWithUsage } from '../types';

export const getBudgets = async (): Promise<readonly BudgetWithUsage[]> => {
  const response = await httpClient.get<unknown>('/budgets/overview');

  return budgetsWithUsageSchema.parse(response.data);
};
