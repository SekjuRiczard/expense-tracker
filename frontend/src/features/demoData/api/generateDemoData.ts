import { httpClient } from '../../../shared/api';
import { generateDemoDataResponseSchema } from '../schemas';
import type { GenerateDemoDataResponse } from '../types';

export const generateDemoData = async (): Promise<GenerateDemoDataResponse> => {
  const response = await httpClient.post<unknown>('/admin/demo-data');
  return generateDemoDataResponseSchema.parse(response.data);
};
