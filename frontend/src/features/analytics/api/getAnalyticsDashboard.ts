import {
  httpClient,
} from '../../../shared/api';
import {
  analyticsDashboardSchema,
} from '../schemas';
import type {
  AnalyticsDashboard,
  AnalyticsDashboardParams,
} from '../types';

export const getAnalyticsDashboard = async (
  params: AnalyticsDashboardParams,
): Promise<AnalyticsDashboard> => {
  const response = await httpClient.get<unknown>(
    '/analytics/dashboard',
    {
      params,
    },
  );

  return analyticsDashboardSchema.parse(response.data);
};
