import { httpClient } from '../../../shared/api';
import { analyticsDashboardSchema } from '../schemas';
import type {
  AnalyticsDashboard,
  AnalyticsDashboardParams,
} from '../types';

const buildQueryParams = (
  params: AnalyticsDashboardParams,
): Record<string, string> => {
  const result: Record<string, string> = {};

  for (const [key, value] of Object.entries(params)) {
    if (value === undefined || value === null || value === '') {
      continue;
    }
    result[key] = String(value);
  }

  return result;
};

export const getAnalyticsDashboard = async (
  params: AnalyticsDashboardParams,
): Promise<AnalyticsDashboard> => {
  const response = await httpClient.get<unknown>('/analytics/dashboard', {
    params: buildQueryParams(params),
  });

  return analyticsDashboardSchema.parse(response.data);
};
