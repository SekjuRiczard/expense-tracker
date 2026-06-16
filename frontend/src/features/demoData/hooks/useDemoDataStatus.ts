import { useQuery } from '@tanstack/react-query';
import { getDemoDataStatus } from '../api';
import { DEMO_DATA_STATUS_QUERY_KEY } from './invalidateDemoDataRelatedQueries';

export interface UseDemoDataStatusOptions {
  readonly enabled?: boolean;
}

export const useDemoDataStatus = (
  { enabled = true }: UseDemoDataStatusOptions = {},
) => {
  return useQuery({
    queryKey: DEMO_DATA_STATUS_QUERY_KEY,
    queryFn: getDemoDataStatus,
    enabled,
  });
};
