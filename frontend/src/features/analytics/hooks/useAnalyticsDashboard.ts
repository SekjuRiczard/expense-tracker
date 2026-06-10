import {
  useQuery,
} from '@tanstack/react-query';
import {
  getAnalyticsDashboard,
} from '../api';
import type {
  AnalyticsDashboardParams,
} from '../types';

export const useAnalyticsDashboard = (
  params: AnalyticsDashboardParams,
) => {
  return useQuery({
    queryKey: [
      'analytics',
      'dashboard',
      params,
    ],
    queryFn: () => {
      return getAnalyticsDashboard(params);
    },
  });
};
