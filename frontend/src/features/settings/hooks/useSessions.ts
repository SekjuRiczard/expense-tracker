import { useQuery } from '@tanstack/react-query';
import { getSessions } from '../api';

export const useSessions = () => {
  return useQuery({
    queryKey: ['settings', 'sessions'],
    queryFn: getSessions,
  });
};
