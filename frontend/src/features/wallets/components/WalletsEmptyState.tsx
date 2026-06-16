import { AccountBalanceWalletOutlined } from '@mui/icons-material';
import { Box, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

export const WalletsEmptyState = () => {
  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        py: 6,
        gap: 1.5,
        color: flowlyPalette.dashboard.textMuted,
      }}
    >
      <Box
        aria-hidden="true"
        sx={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          width: 64,
          height: 64,
          borderRadius: '20px',
          backgroundColor: flowlyPalette.dashboard.indigoSoft,
          color: flowlyPalette.dashboard.indigoDark,
          mb: 0.5,
        }}
      >
        <AccountBalanceWalletOutlined sx={{ fontSize: 28 }} />
      </Box>

      <Typography
        sx={{
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '1rem',
          fontWeight: 700,
        }}
      >
        No wallets yet
      </Typography>

      <Typography
        sx={{
          color: flowlyPalette.dashboard.textSecondary,
          fontSize: '0.85rem',
          textAlign: 'center',
          maxWidth: 280,
        }}
      >
        Create your first wallet to start tracking your finances.
      </Typography>
    </Box>
  );
};
