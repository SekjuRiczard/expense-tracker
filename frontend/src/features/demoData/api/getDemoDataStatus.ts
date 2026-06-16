import { httpClient } from '../../../shared/api';
import { demoDataStatusResponseSchema } from '../schemas';
import type { DemoDataStatusResponse } from '../types';

export const getDemoDataStatus =
  async (): Promise<DemoDataStatusResponse> => {
    const response = await httpClient.get<unknown>('/admin/demo-data/status');
    return demoDataStatusResponseSchema.parse(response.data);
  };
