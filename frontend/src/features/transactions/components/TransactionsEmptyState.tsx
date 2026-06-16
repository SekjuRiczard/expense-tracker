import { ReceiptLongOutlined } from '@mui/icons-material';
import { Box, Typography } from '@mui/material';
import { flowlyPalette } from '../../../app/theme';

export const TransactionsEmptyState = () => {
  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 1,
        py: 7,
        px: 2,
      }}
    >
      <Box
        aria-hidden="true"
        sx={{
          display: 'grid',
          width: 56,
          height: 56,
          placeItems: 'center',
          borderRadius: '16px',
          backgroundColor: flowlyPalette.dashboard.indigoSoft,
          color: flowlyPalette.dashboard.indigoDark,
          mb: 0.5,
        }}
      >
        <ReceiptLongOutlined sx={{ fontSize: 26 }} />
      </Box>

      <Typography
        sx={{
          color: flowlyPalette.dashboard.textPrimary,
          fontSize: '0.95rem',
          fontWeight: 700,
        }}
      >
        No transactions found.
      </Typography>

      <Typography
        sx={{
          color: flowlyPalette.dashboard.textSecondary,
          fontSize: '0.83rem',
          textAlign: 'center',
        }}
      >
        Add your first transaction or adjust filters.
      </Typography>
    </Box>
  );
};
