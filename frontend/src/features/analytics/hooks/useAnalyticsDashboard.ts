import { keepPreviousData, useQuery } from '@tanstack/react-query';
import { getAnalyticsDashboard } from '../api';
import type { AnalyticsDashboardParams } from '../types';

export const useAnalyticsDashboard = (
  params: AnalyticsDashboardParams,
  options?: { readonly enabled?: boolean },
) => {
  return useQuery({
    queryKey: ['analytics', 'dashboard', params],
    queryFn: () => getAnalyticsDashboard(params),
    placeholderData: keepPreviousData,
    enabled: options?.enabled ?? true,
  });
};
