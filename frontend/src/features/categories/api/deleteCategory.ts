import { httpClient } from '../../../shared/api';

export const deleteCategory = async (id: number): Promise<void> => {
  await httpClient.delete(`/categories/${id}`);
};
