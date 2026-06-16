import {
  Alert,
  Box,
  Link,
  Skeleton,
  Stack,
  Typography,
} from '@mui/material';
import {
  Link as RouterLink,
} from 'react-router-dom';
import { flowlyPalette, } from '../../../../app/theme';
import type {
  Wallet,
} from '../../../wallets';
import { WalletPreviewCard, } from './WalletPreviewCard';

export interface WalletPreviewSectionProps {
  readonly data?: readonly Wallet[];
  readonly error: boolean;
  readonly loading: boolean;
}

export const WalletPreviewSection = ({
  data,
  error,
  loading,
}: WalletPreviewSectionProps) => {
  return (
    <Box
      component="section"
      sx={{
        mt: 3,
      }}
    >
      <Stack
        sx={{
          alignItems: 'center',
          flexDirection: 'row',
          gap: 1.25,
          mb: 1.5,
        }}
      >
        <Typography
          component="h2"
          sx={{
            color: flowlyPalette.dashboard.textPrimary,
            fontSize: '1.1rem',
            fontWeight: 900,
            letterSpacing: '-0.025em',
          }}
        >
          Your wallets
        </Typography>

        <Link
          component={RouterLink}
          to="/wallets"
          underline="hover"
          sx={{
            color: flowlyPalette.dashboard.indigoDark,
            fontSize: '0.83rem',
            fontWeight: 800,
          }}
        >
          View all ({data?.length ?? 0}) ›
        </Link>
      </Stack>

      {error && (
        <Alert severity="error">
          Wallets could not be loaded.
        </Alert>
      )}

      {!error && loading && (
        <Box
          sx={{
            display: 'grid',
            gridTemplateColumns: {
              xs: '1fr',
              sm: 'repeat(2, minmax(0, 1fr))',
              lg: 'repeat(3, minmax(0, 1fr))',
            },
            gap: 2,
          }}
        >
          {Array.from({
            length: 3,
          }).map((_, index) => (
            <Skeleton
              key={`wallet-skeleton-${index}`}
              height={142}
              variant="rounded"
            />
          ))}
        </Box>
      )}

      {!error && !loading && data?.length === 0 && (
        <Alert severity="info">
          No wallets yet. Create your first wallet to get started.
        </Alert>
      )}

      {!error && !loading && Boolean(data?.length) && (
        <Box
          sx={{
            display: 'grid',
            gridTemplateColumns: {
              xs: '1fr',
              sm: 'repeat(2, minmax(0, 1fr))',
              lg: 'repeat(3, minmax(0, 1fr))',
            },
            gap: 2,
          }}
        >
          {data
            ?.slice(0, 3)
            .map((wallet) => (
              <WalletPreviewCard
                key={wallet.id}
                wallet={wallet}
              />
            ))}
        </Box>
      )}
    </Box>
  );
};
