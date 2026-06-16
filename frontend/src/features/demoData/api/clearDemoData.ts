import { httpClient } from '../../../shared/api';
import { clearDemoDataResponseSchema } from '../schemas';
import type { ClearDemoDataResponse } from '../types';

export const clearDemoData = async (): Promise<ClearDemoDataResponse> => {
  const response = await httpClient.delete<unknown>('/admin/demo-data');
  return clearDemoDataResponseSchema.parse(response.data);
};
