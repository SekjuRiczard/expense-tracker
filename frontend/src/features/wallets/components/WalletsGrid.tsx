import { Alert, Box } from '@mui/material';
import type { Wallet } from '../types';
import { WalletCard } from './WalletCard';
import { CreateWalletCard } from './CreateWalletCard';
import { WalletsEmptyState } from './WalletsEmptyState';
import { WalletsSkeleton } from './WalletsSkeleton';

export interface WalletsGridProps {
  readonly wallets: readonly Wallet[];
  readonly loading: boolean;
  readonly error: boolean;
  readonly onCreateClick: () => void;
  readonly onViewDetails: (wallet: Wallet) => void;
  readonly onEdit: (wallet: Wallet) => void;
  readonly onDelete: (wallet: Wallet) => void;
}

export const WalletsGrid = ({
  wallets,
  loading,
  error,
  onCreateClick,
  onViewDetails,
  onEdit,
  onDelete,
}: WalletsGridProps) => {
  if (loading) {
    return <WalletsSkeleton />;
  }

  if (error) {
    return (
      <Alert severity="error" sx={{ borderRadius: '12px' }}>
        Failed to load wallets.
      </Alert>
    );
  }

  return (
    <>
      {wallets.length === 0 && <WalletsEmptyState />}

      <Box
        sx={{
          display: 'grid',
          gridTemplateColumns: {
            xs: '1fr',
            md: 'repeat(2, minmax(0, 1fr))',
            lg: 'repeat(3, minmax(0, 1fr))',
          },
          gap: 2.5,
        }}
      >
        {wallets.map((wallet) => (
          <WalletCard
            key={wallet.id}
            onDelete={() => { onDelete(wallet); }}
            onEdit={() => { onEdit(wallet); }}
            onViewDetails={() => { onViewDetails(wallet); }}
            wallet={wallet}
          />
        ))}

        <CreateWalletCard onClick={onCreateClick} />
      </Box>
    </>
  );
};
