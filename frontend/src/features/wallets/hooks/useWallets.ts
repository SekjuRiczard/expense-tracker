import {
  useQuery,
} from '@tanstack/react-query';
import {
  getWallets,
} from '../api';

export const useWallets = () => {
  return useQuery({
    queryKey: [
      'wallets',
    ],
    queryFn: getWallets,
  });
};
