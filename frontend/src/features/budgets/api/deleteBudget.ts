import { httpClient } from '../../../shared/api';

export const deleteBudget = async (id: number): Promise<void> => {
  await httpClient.delete(`/budgets/${id}`);
};
