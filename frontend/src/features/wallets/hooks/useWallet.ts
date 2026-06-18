import { useQuery } from '@tanstack/react-query';
import { getWallet } from '../api';

export const useWallet = (id: number | null) => {
  return useQuery({
    queryKey: ['wallets', id],
    queryFn: () => getWallet(id!),
    enabled: id !== null,
  });
};
