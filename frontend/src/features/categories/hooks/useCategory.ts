import { useQuery } from '@tanstack/react-query';
import { getCategory } from '../api';

export const useCategory = (id: number | null) => {
  return useQuery({
    queryKey: ['categories', id],
    queryFn: () => getCategory(id!),
    enabled: id !== null,
  });
};
