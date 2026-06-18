import { useQuery } from '@tanstack/react-query';
import { getTransaction } from '../api';

export const useTransaction = (id: number | null) => {
  return useQuery({
    queryKey: ['transactions', id],
    queryFn: () => getTransaction(id!),
    enabled: id !== null,
  });
};
